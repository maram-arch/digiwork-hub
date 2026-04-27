<?php

require_once __DIR__ . '/../config/config.php';

class UserModel
{
	private PDO $db;

	public function __construct()
	{
		$this->db = Config::getConnexion();
	}

	public function getAll(): array
	{
		$stmt = $this->db->query('SELECT id_user, email, role, tel FROM user ORDER BY id_user DESC');
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getById(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id_user, email, mdp, role, tel FROM user WHERE id_user = :id');
		$stmt->execute(['id' => $id]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		return $user ?: null;
	}

	public function getByEmail(string $email): ?array
	{
		$stmt = $this->db->prepare('SELECT id_user, email, mdp, role, tel FROM user WHERE email = :email LIMIT 1');
		$stmt->execute(['email' => $email]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		return $user ?: null;
	}

	public function create(string $email, string $password, string $role, string $tel): bool
	{
		$stmt = $this->db->prepare('INSERT INTO user (email, mdp, role, tel) VALUES (:email, :mdp, :role, :tel)');
		return $stmt->execute([
			'email' => $email,
			'mdp' => $password,
			'role' => $role,
			'tel' => $tel,
		]);
	}

	public function update(int $id, string $email, string $password, string $role, string $tel): bool
	{
		$stmt = $this->db->prepare('UPDATE user SET email = :email, mdp = :mdp, role = :role, tel = :tel WHERE id_user = :id');
		return $stmt->execute([
			'id' => $id,
			'email' => $email,
			'mdp' => $password,
			'role' => $role,
			'tel' => $tel,
		]);
	}

	public function delete(int $id): bool
	{
		$stmt = $this->db->prepare('DELETE FROM user WHERE id_user = :id');
		return $stmt->execute(['id' => $id]);
	}

	public function count(): int
	{
		$stmt = $this->db->query('SELECT COUNT(*) as total FROM user');
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int) ($result['total'] ?? 0);
	}

	public function getByRole(string $role): array
	{
		$stmt = $this->db->prepare('SELECT id_user, email, role, tel FROM user WHERE role = :role ORDER BY id_user DESC');
		$stmt->execute(['role' => $role]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAuthRecords(): array
	{
		$stmt = $this->db->query('SELECT id_user, mdp FROM user ORDER BY id_user ASC');
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getOnlineUsers(int $minutes = 15): array
	{
		// Only users explicitly marked `is_online = 1` are considered connected.
		$stmt = $this->db->prepare(
			"SELECT id_user, email, role, tel, last_activity
			 FROM user
			 WHERE is_online = 1
			 ORDER BY last_activity DESC"
		);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function markOnline(int $id): bool
	{
		$stmt = $this->db->prepare('UPDATE user SET is_online = 1, last_activity = NOW() WHERE id_user = :id');
		return $stmt->execute(['id' => $id]);
	}

	public function touchActivity(int $id): bool
	{
		$stmt = $this->db->prepare('UPDATE user SET last_activity = NOW(), is_online = 1 WHERE id_user = :id');
		return $stmt->execute(['id' => $id]);
	}

	public function markOffline(int $id): bool
	{
		$stmt = $this->db->prepare('UPDATE user SET is_online = 0 WHERE id_user = :id');
		return $stmt->execute(['id' => $id]);
	}

	public function updatePassword(int $id, string $password): bool
	{
		$stmt = $this->db->prepare('UPDATE user SET mdp = :mdp WHERE id_user = :id');
		return $stmt->execute([
			'id' => $id,
			'mdp' => $password,
		]);
	}
}
