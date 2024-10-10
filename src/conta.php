<?php

include_once 'db.php';

class conta {

    private $conn;

    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function getAll() {
        $sql = "SELECT 
            e-mail, 
            nome completo, 
            senha, 
            repita a senha,
        FROM conta";
        $result = $this->conn->query($sql);

        $data = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    function getById($codigo) {
        $sql = "SELECT 
            e-mail, 
            nome completo, 
            senha, 
            repita a senha,
        FROM conta
        WHERE codigo = ?";
        $stm = $this->conn->prepare($sql);

        $stm->bind_param('i', $codigo);
        $stm->execute();

        $result = $stm->get_result();

        $data = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    function deleteById($codigo) {
        $sql = "DELETE FROM pessoa WHERE codigo = ?";
        $stm = $this->conn->prepare($sql);

        $stm->bind_param('i', $codigo);
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro excluído com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao excluir registro'];
    }

    function updateById($codigo, $data) {
        $sql = "UPDATE conta SET 
            e-mail = ?,
            nome completo = ?,
            senha = ?
            repita a senha = ?
        WHERE codigo = ?";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'ssssi', 
            $data['e-mail'], 
            $data['nome completo'], 
            $data['senha'], 
            $data['repita a senha'], 
            $codigo
        );
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro atualizado com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao atualizar registro'];
    }

    function create($data) {
        $sql = "INSERT INTO pessoa (e-mail, nome completo, senha, repita a senha) VALUES (?, ?, ?)";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'ssss', 
            $data['e-mail'], 
            $data['nome completo'], 
            $data['senha']
            $data['repita a senha']
        );
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro criado com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao criar registro'];
    }
}

$allowed_methods = [
    'GET',
    'POST',
    'PUT',
    'DELETE'
];

if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode( [
        'status' => 'error',
        'msg' => 'Invalid Request'
    ] );
}

$pessoa = new cadastro($conn);

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    echo json_encode($conta->deleteById($_GET['codigo']));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($conta->updateById($_GET['codigo'], $data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($conta->create($data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'pessoa/cadastro')) {
        echo json_encode($conta->getById($_GET['codigo']));
        return;
    }

    echo json_encode($conta->getAll());
    return;
}