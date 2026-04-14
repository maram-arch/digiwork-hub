<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/UserModel.php';

class UserController
{
    private const ALLOWED_ROLES = ['condidat', 'admin', 'entreprise', 'sponsor'];

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function listUsers(): array
    {
        return $this->userModel->getAll();
    }

    public function findUser(int $id): ?array
    {
        return $this->userModel->getById($id);
    }

    public function createUser(array $data): array
    {
        $validation = $this->validateUserData($data, false);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        if ($this->userModel->getByEmail($validation['clean']['email'])) {
            return ['success' => false, 'errors' => ['Cet email existe deja.']];
        }

        $created = $this->userModel->create(
            $validation['clean']['email'],
            $validation['clean']['password'],
            $validation['clean']['role'],
            $validation['clean']['tel']
        );

        return $created
            ? ['success' => true, 'message' => 'Utilisateur ajoute avec succes.']
            : ['success' => false, 'errors' => ['Creation impossible.']];
    }

    public function updateUser(int $id, array $data): array
    {
        $validation = $this->validateUserData($data, true);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $existing = $this->userModel->getByEmail($validation['clean']['email']);
        if ($existing && (int) $existing['id_user'] !== $id) {
            return ['success' => false, 'errors' => ['Cet email est deja utilise par un autre compte.']];
        }

        $currentUser = $this->userModel->getById($id);
        if (!$currentUser) {
            return ['success' => false, 'errors' => ['Utilisateur introuvable.']];
        }

        $password = $validation['clean']['password'] !== ''
            ? $validation['clean']['password']
            : $currentUser['mdp'];

        $updated = $this->userModel->update(
            $id,
            $validation['clean']['email'],
            $password,
            $validation['clean']['role'],
            $validation['clean']['tel']
        );

        return $updated
            ? ['success' => true, 'message' => 'Utilisateur modifie avec succes.']
            : ['success' => false, 'errors' => ['Mise a jour impossible.']];
    }

    public function deleteUser(int $id): array
    {
        $deleted = $this->userModel->delete($id);
        return $deleted
            ? ['success' => true, 'message' => 'Utilisateur supprime avec succes.']
            : ['success' => false, 'errors' => ['Suppression impossible.']];
    }

    public function login(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));

        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Email et mot de passe obligatoires.'];
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user || $user['mdp'] !== $password) {
            return ['success' => false, 'message' => 'Identifiants invalides.'];
        }

        return [
            'success' => true,
            'message' => 'Connexion reussie.',
            'role' => 'candidat',
            'user' => [
                'id' => (int) $user['id_user'],
                'email' => $user['email'],
                'role' => $user['role'],
                'tel' => $user['tel'],
            ],
        ];
    }

    public function signup(array $data): array
    {
        return $this->createUser([
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
            'role' => $data['role'] ?? 'condidat',
            'tel' => $data['tel'] ?? '',
        ]);
    }

    public function getAllowedRoles(): array
    {
        return self::ALLOWED_ROLES;
    }

    private function validateUserData(array $data, bool $isUpdate): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));
        $role = strtolower(trim((string) ($data['role'] ?? 'condidat')));
        $tel = trim((string) ($data['tel'] ?? ''));

        $errors = [];

        if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            $errors[] = 'Email invalide.';
        }

        if ($isUpdate) {
            if ($password !== '' && strlen($password) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
            }
        } elseif (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
        }

        if (!preg_match('/^[0-9]{8,15}$/', $tel)) {
            $errors[] = 'Le numero de telephone doit contenir entre 8 et 15 chiffres.';
        }

        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            $errors[] = 'Role invalide.';
            $role = 'condidat';
        }

        return [
            'errors' => $errors,
            'clean' => [
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'tel' => $tel,
            ],
        ];
    }
}
