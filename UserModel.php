<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {
    protected $table = 'users';

    public function create($data) {
        $sql = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        $stmt = $this->db->prepare($sql);
        // Password harus di-hash demi keamanan
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $stmt->execute($data);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}