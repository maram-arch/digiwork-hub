<?php

class Forum
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM forums ORDER BY date_publication DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($titre, $contenu, $id_users)
    {
        $stmt = $this->db->prepare("
            INSERT INTO forums (titre, contenu, date_publication, id_user)
            VALUES (?, ?, NOW(), ?)
        ");
        return $stmt->execute([$titre, $contenu, $id_users]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM forums WHERE id_publication = ?");
        return $stmt->execute([$id]);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM forums WHERE id_publication = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $titre, $contenu)
    {
        $stmt = $this->db->prepare("
            UPDATE forums 
            SET titre = ?, contenu = ?
            WHERE id_publication = ?
        ");
        return $stmt->execute([$titre, $contenu, $id]);
    }
}
?>