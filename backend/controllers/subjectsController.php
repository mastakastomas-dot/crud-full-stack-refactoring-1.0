<?php
require_once("./repositories/subjects.php");

function handleGet($conn) {
    if (isset($_GET['id'])) {
        $subject = getSubjectById($conn, $_GET['id']);
        echo json_encode($subject);
    } 
    //2.0
    else if (isset($_GET['page']) && isset($_GET['limit'])) 
    {
        $page = (int)$_GET['page'];
        $limit = (int)$_GET['limit'];
        $offset = ($page - 1) * $limit;

        $subjects = getPaginatedSubjects($conn, $limit, $offset);
        $total = getTotalSubjects($conn);

        echo json_encode([
            'subjects' => $subjects,
            'total' => $total
        ]);
    } else {
        $subjects = getAllSubjects($conn);
        echo json_encode([
            'subjects' => $subjects,
            'total' => count($subjects)
        ]);
    }
}


function handlePost($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    // 1. VALIDACIÓN: Revisar si la materia ya existe
    $existingSubject = getSubjectByName($conn, $input['name']); // Necesitas crear esta función

    if ($existingSubject) {
        // 2. Si existe, enviar el error 409 (Conflict) que el frontend espera
        http_response_code(409); 
        echo json_encode(["error" => "La materia ya existe"]);
        return; // Detener la ejecución
    }

    // 3. Si no existe, crearla
    $result = createSubject($conn, $input['name']);
    
    if ($result['inserted'] > 0) {
        http_response_code(201); // 201 (Created) es más correcto para un POST
        echo json_encode(["message" => "Materia creada correctamente"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo crear"]);
    }
}

function handlePut($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    $result = updateSubject($conn, $input['id'], $input['name']);
    if ($result['updated'] > 0) 
    {
        echo json_encode(["message" => "Materia actualizada correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo actualizar"]);
    }
}

function handleDelete($conn) 
{
  $input = json_decode(file_get_contents("php://input"), true);
    
    // Verificar si la materia tiene relaciones
    if (hasSubjectRelations($conn, $input['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "No se puede eliminar la materia porque tiene estudiantes inscritos"]);
        return;
    }

    $result = deleteSubject($conn, $input['id']);
    if ($result['deleted'] > 0) 
    {
        echo json_encode(["message" => "Materia eliminada correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo eliminar"]);
    }
}
?>
