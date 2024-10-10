<?php

include_once 'db.php';

class signo_biologico {

    private $conn;

    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function getAll() {
        $sql = "SELECT 
            feminino,
            masculino
        FROM signo_biologico";
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
            feminino,
            masculino
        FROM signo_biologico
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
        $sql = "DELETE FROM signo_biologico WHERE codigo = ?";
        $stm = $this->conn->prepare($sql);

        $stm->bind_param('i', $codigo);
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro excluído com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao excluir registro'];
    }

    function updateById($codigo, $data) {
        $sql = "UPDATE signo_biologico SET 
            feminino = ?,
            masculinop = ?,
        WHERE codigo = ?";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'ss', 
            $data['feminino'], 
            $data['masculino'],  
            $codigo
        );
        $stm->execute();

        if (!$stm->error) {
            return ['status' => 'ok', 'msg' => 'Registro atualizado com sucesso'];
        }

        return ['status' => 'error', 'msg' => 'Falha ao atualizar registro'];
    }

    function create($data) {
        $sql = "INSERT INTO signo_biologico (feminino, masculino) VALUES (?, ?, ?)";

        $stm = $this->conn->prepare($sql);

        $stm->bind_param(
            'ss', 
            $data['feminino'], 
            $data['masculino'], 
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

$signo_biologico = new signo_biologico($conn);

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    echo json_encode($signo_biologico->deleteById($_GET['codigo']));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($signo_biologico->updateById($_GET['codigo'], $data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($signo_biologico->create($data));
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'pessoa/cadastro')) {
        echo json_encode($signo_biologico->getById($_GET['codigo']));
        return;
    }

    echo json_encode($signo_biologico->getAll());
    return;
}