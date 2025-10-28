/**
*    File        : frontend/js/controllers/studentsController.js
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 3.0 ( prototype )
*/

import { studentsAPI } from '../api/studentsAPI.js';


//For pagination:
let currentPage = 1;
let totalPages = 1;
const limit = 5;


document.addEventListener('DOMContentLoaded', () => 
{
    loadStudents();
    setupFormHandler();
    setupCancelHandler();
    setupPaginationControls();
     
});
 // Actualizada 
function setupFormHandler()
{
    const form = document.getElementById('studentForm');
    

    const formMessageDiv = document.getElementById('formError');

    form.addEventListener('submit', async e => 
    {
        e.preventDefault();
        formMessageDiv.style.display = 'none'; // Ocultamos errores viejos antes de enviar
        const student = getFormData();
    
        try 
        {   let response;
            if (student.id) // Se edita un estudiante
            {
               response =  await studentsAPI.update(student); 
            } 
            else // Se crea un estudiante
            {
               response =  await studentsAPI.create(student); 
            }

            if (response.error) {
                throw new Error(response.error); // Si hubo un error pasa a catch
            }
            
            if (response.message) {
                // Mostramos el mensaje de éxito
                formMessageDiv.textContent = response.message;
                formMessageDiv.classList.remove('w3-red'); // Cambiamos el color a verde
                formMessageDiv.classList.add('w3-green');  
                formMessageDiv.style.display = 'block';

                
                setTimeout(() => {
                    clearForm();
                    loadStudents();
                    formMessageDiv.style.display = 'none'; 
                }, 2000); //Despues de 2 segundos se oculta 
            } else {
                // Si no vino ningun mensaje
                clearForm();
                loadStudents();
            }
        }
        catch (err)
        {
            formMessageDiv.textContent = err.message;
            formMessageDiv.classList.remove('w3-green'); // Cambiamos el color a rojo
            formMessageDiv.classList.add('w3-red');     
            formMessageDiv.style.display = 'block';


            setTimeout(() => {
                
                loadStudents();
                formMessageDiv.style.display = 'none';
            }, 5000); // Despues de 5 segundos se oculta
            
        }
    });
}

function setupCancelHandler()
{
    const cancelBtn = document.getElementById('cancelBtn');
    cancelBtn.addEventListener('click', () => 
    {
        document.getElementById('studentId').value = '';
    });
}

function setupPaginationControls() 
{
    document.getElementById('prevPage').addEventListener('click', () => 
    {
        if (currentPage > 1) 
        {
            currentPage--;
            loadStudents();
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => 
    {
        if (currentPage < totalPages) 
        {
            currentPage++;
            loadStudents();
        }
    });

    document.getElementById('resultsPerPage').addEventListener('change', e => 
    {
        currentPage = 1;
        loadStudents();
    });
}
  
function getFormData()
{
    return {
        id: document.getElementById('studentId').value.trim(),
        fullname: document.getElementById('fullname').value.trim(),
        email: document.getElementById('email').value.trim(),
        age: parseInt(document.getElementById('age').value.trim(), 10)
    };
}
  
function clearForm()
{
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
}
  
async function loadStudents()
{
    try 
    {
        const resPerPage = parseInt(document.getElementById('resultsPerPage').value, 10) || limit;
        const data = await studentsAPI.fetchPaginated(currentPage, resPerPage);
        console.log(data);
        renderStudentTable(data.students);
        totalPages = Math.ceil(data.total / resPerPage);
        document.getElementById('pageInfo').textContent = `Página ${currentPage} de ${totalPages}`;
    } 
    catch (err) 
    {
        console.error('Error cargando estudiantes:', err.message);
    }
}
  
function renderStudentTable(students)
{
    const tbody = document.getElementById('studentTableBody');
    tbody.replaceChildren();
  
    students.forEach(student => 
    {
        const tr = document.createElement('tr');
    
        tr.appendChild(createCell(student.fullname));
        tr.appendChild(createCell(student.email));
        tr.appendChild(createCell(student.age.toString()));
        tr.appendChild(createActionsCell(student));
    
        tbody.appendChild(tr);
    });
}
  
function createCell(text)
{
    const td = document.createElement('td');
    td.textContent = text;
    return td;
}
  
function createActionsCell(student)
{
    const td = document.createElement('td');
  
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Editar';
    editBtn.className = 'w3-button w3-blue w3-small';
    editBtn.addEventListener('click', () => fillForm(student));
  
    const deleteBtn = document.createElement('button');
    deleteBtn.textContent = 'Borrar';
    deleteBtn.className = 'w3-button w3-red w3-small w3-margin-left';
    deleteBtn.addEventListener('click', () => confirmDelete(student.id));
  
    td.appendChild(editBtn);
    td.appendChild(deleteBtn);
    return td;
}
  
function fillForm(student)
{
    document.getElementById('studentId').value = student.id;
    document.getElementById('fullname').value = student.fullname;
    document.getElementById('email').value = student.email;
    document.getElementById('age').value = student.age;
}
  
async function confirmDelete(id) 
{
    const formMessageDiv = document.getElementById('formError');
   
    if (!confirm('¿Estás seguro que deseas borrar este estudiante?')) return;  
    try 
    {
      let response;
      response =  await studentsAPI.remove(id);             
      if (response.error) {
         throw new Error(response.error); // Si hubo un error pasa a catch
     }
      if (response.message){
         loadStudents();
     }
    }
   
    catch (err) 
    {

                formMessageDiv.textContent = err.message;
                formMessageDiv.classList.add('w3-red');  
                formMessageDiv.style.display = 'block';
    }
}

