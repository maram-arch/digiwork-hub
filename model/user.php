<?php

class User
{
	private string $id_user;
	private string $email;
	private string $mdp;
	private string $role;
	private string $tel;

	public function __construct(string $id_user, string $email, string $mdp, string $role, string $tel)
	{
		$this->id_user = $id_user;
		$this->email = $email;
		$this->mdp = $mdp;
		$this->role = $role;
		$this->tel = $tel;
	}

	public function getId_user(): string
	{
		return $this->id_user;
	}

	public function setId_user(string $id_user): void
	{
		$this->id_user = $id_user;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getMdp(): string
	{
		return $this->mdp;
	}

	public function setMdp(string $mdp): void
	{
		$this->mdp = $mdp;
	}

	public function getRole(): string
	{
		return $this->role;
	}

	public function setRole(string $role): void
	{
		$this->role = $role;
	}

	public function getTel(): string
	{
		return $this->tel;
	}

	public function setTel(string $tel): void
	{
		$this->tel = $tel;
	}

	public function show(): void
	{
		echo "<table border='1' cellpadding='5'>";
		echo "<tr><th>ID</th><th>Email</th><th>Password</th><th>Role</th><th>Phone</th></tr>";
		echo "<tr>";
		echo "<td>" . htmlspecialchars($this->id_user) . "</td>";
		echo "<td>" . htmlspecialchars($this->email) . "</td>";
		echo "<td>***</td>";
		echo "<td>" . htmlspecialchars($this->role) . "</td>";
		echo "<td>" . htmlspecialchars($this->tel) . "</td>";
		echo "</tr>";
		echo "</table>";
	}
}

