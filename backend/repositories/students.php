<?php
/**
*    File        : backend/models/students.php
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 3.0 ( prototype )
*/

function getAllStudents($conn) 
{
    $sql = "SELECT * FROM students";

    //MYSQLI_ASSOC devuelve un array ya listo para convertir en JSON:
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getPaginatedStudents($conn, $limit, $offset) 
{
    $stmt = $conn->prepare("SELECT * FROM students LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalStudents($conn) 
{
    $sql = "SELECT COUNT(*) AS total FROM students";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

function getStudentById($conn, $id) 
{
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    //fetch_assoc() devuelve un array asociativo ya listo para convertir en JSON de una fila:
    return $result->fetch_assoc(); 
}

function createStudent($conn, $fullname, $email, $age) 
{   // Verificamos si el nuevo email ya está en uso por otro estudiante
    if (checkEmailExists($conn, $email)) {
        
        return [
            'inserted' => 0,
            'id' => null,
            'error' => 'El correo electrónico ya está en uso por otro usuario.'
        ];
    }


    $sql = "INSERT INTO students (fullname, email, age) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $fullname, $email, $age);
    $stmt->execute();

    //Se retorna un arreglo con la cantidad de filas insertadas 
    //e id insertado para validar en el controlador:
    return 
    [
        'inserted' => $stmt->affected_rows,        
        'id' => $conn->insert_id
    ];
}

function updateStudent($conn, $id, $fullname, $email, $age)
{
    // Verificamos si el nuevo email ya está en uso por otro estudiante
    if (checkEmailExists($conn, $email, $id)) {
        return [
            'updated' => 0,
            'error' => 'El correo electrónico ya está en uso por otro usuario.'
        ];
    }

    $sql = "UPDATE students SET fullname = ?, email = ?, age = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $fullname, $email, $age, $id);
    $stmt->execute();

    //Se retorna fila afectadas para validar en controlador:
    return ['updated' => $stmt->affected_rows];
}

function deleteStudent($conn, $id) 
{
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    //Se retorna fila afectadas para validar en controlador
    return ['deleted' => $stmt->affected_rows];
}
// Nuevo: verifica si existe el email
function checkEmailExists($conn, $email, $ignoreId = null)
{
    $sql = "SELECT id FROM students WHERE email = ?"; // Selecciona la id que pertenece al email
    if ($ignoreId) { // Si se esta editando un usuario y no creando, ignoreId no es null entonces entra al if
        $sql .= " AND id != ?"; // Verifica si hay otro estudiante que tenga ese email y su id sea distinta de la dada
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($ignoreId) { // Depende de si edita o crea, le hace bind a 1 o 2 parametros
        $stmt->bind_param("si", $email, $ignoreId);// Edita
    } else {
        $stmt->bind_param("s", $email);// Crea
    }
    
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0; // Si obtuvo un resultado mayor a uno existe el mail
    $stmt->close();
    
    return $exists;
}
?>