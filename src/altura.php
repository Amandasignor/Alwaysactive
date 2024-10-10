<?php

include_once 'db.php';

class Pessoa {

    private $conn;

    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function getAll() {
        $sql = "SELECT 
            codigo, 
            nome, 
            documento, 
            DATE_FORMAT(data_cadastro, '%d/%m/%Y %H:%i:%s') data_cadastro,
            DATE_FORMAT(data_nascimento, '%d/%m/%Y') data_nascimento
        FROM pessoa";
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
            codigo, 
            nome, 
            documento, 
            DATE_FORMAT(data_nascimento, '%Y-%m-%d') data_nascimento
        FROM pessoa
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
        $sql = "UPDATE pessoa SET 
            nome = ?,
            documento = ?,
            data_nascimento = ?
        WHERE codigo = ?";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'sssi', 
            $data['nome'], 
            $data['documento'], 
            $data['nascimento'], 
            $codigo
        );
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro atualizado com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao atualizar registro'];
    }

    function create($data) {
        $sql = "INSERT INTO pessoa (nome, documento, data_nascimento) VALUES (?, ?, ?)";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'sss', 
            $data['nome'], 
            $data['documento'], 
            $data['nascimento']
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

$pessoa = new Pessoa($conn);

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    echo json_encode($pessoa->deleteById($_GET['codigo']));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($pessoa->updateById($_GET['codigo'], $data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($pessoa->create($data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'pessoa/cadastro')) {
        echo json_encode($pessoa->getById($_GET['codigo']));
        return;
    }

    echo json_encode($pessoa->getAll());
    return;
}