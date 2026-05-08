<?php

require_once __DIR__ . '/../config/config.php';

class OtpModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    /**
     * Génère un nouveau code OTP pour l'utilisateur, supprime les anciens du même contexte et l'insère hashé en base.
     *
     * @param int    $userId  Identifiant de l'utilisateur
     * @param string $context Contexte de l'OTP : 'signup' (défaut) ou 'reset'
     * @return string         Code OTP en clair à 6 chiffres (jamais persisté)
     */
    public function createOtp(int $userId, string $context = 'signup'): string
    {
        // Supprimer uniquement les anciens OTP du même utilisateur ET du même contexte
        // (ne pas invalider un OTP signup lors d'un reset simultané)
        $stmt = $this->db->prepare('DELETE FROM otp_verification WHERE id_user = :id AND context = :context');
        $stmt->execute(['id' => $userId, 'context' => $context]);

        // Générer un code à 6 chiffres via CSPRNG
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Hasher le code avec bcrypt
        $hash = password_hash($code, PASSWORD_BCRYPT);

        // Insérer en base avec expiration à 10 minutes et le contexte
        $stmt = $this->db->prepare(
            'INSERT INTO otp_verification (id_user, otp_hash, expires_at, context)
             VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE), :context)'
        );
        $stmt->execute(['id' => $userId, 'hash' => $hash, 'context' => $context]);

        return $code;
    }

    /**
     * Vérifie un code OTP soumis par l'utilisateur.
     *
     * @param int    $userId  Identifiant de l'utilisateur
     * @param string $code    Code OTP en clair soumis par l'utilisateur
     * @param string $context Contexte de l'OTP : 'signup' (défaut) ou 'reset'
     * @return string         'valid' | 'invalid' | 'expired' | 'used'
     */
    public function verifyOtp(int $userId, string $code, string $context = 'signup'): string
    {
        $stmt = $this->db->prepare(
            'SELECT *, (expires_at < NOW()) AS is_expired
             FROM otp_verification
             WHERE id_user = :id AND context = :context
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId, 'context' => $context]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return 'invalid';
        }

        // Vérifier l'expiration (comparaison faite côté MySQL pour éviter les décalages de fuseau horaire)
        if ((int) $row['is_expired'] === 1) {
            return 'expired';
        }

        // Vérifier si déjà utilisé
        if ((int) $row['used'] === 1) {
            return 'used';
        }

        // Vérifier le code (comparaison à temps constant via password_verify)
        if (!password_verify($code, $row['otp_hash'])) {
            return 'invalid';
        }

        // Marquer comme utilisé
        $upd = $this->db->prepare('UPDATE otp_verification SET used = 1 WHERE id = :id');
        $upd->execute(['id' => $row['id']]);

        return 'valid';
    }

    /**
     * Retourne le nombre de secondes écoulées depuis la création du dernier OTP de l'utilisateur pour un contexte donné.
     *
     * @param int    $userId  Identifiant de l'utilisateur
     * @param string $context Contexte de l'OTP : 'signup' (défaut) ou 'reset'
     * @return int|null       Secondes écoulées ou null si aucun OTP
     */
    public function getSecondsSinceLastOtp(int $userId, string $context = 'signup'): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed
             FROM otp_verification
             WHERE id_user = :id AND context = :context
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId, 'context' => $context]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['elapsed'] : null;
    }
}
