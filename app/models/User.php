<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'sp_users';
    
    protected array $fillable = [
        'name',
        'email',
        'password_hash',
        'locale'
    ];

    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        return $this->create($data);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        return $this->update($id, ['password_hash' => $passwordHash]);
    }

    public function findByEmail(string $email): ?array
    {
        error_log("User::findByEmail called with: $email");
        $result = $this->first('email', $email);
        error_log("User::findByEmail result: " . json_encode($result));
        return $result;
    }
}