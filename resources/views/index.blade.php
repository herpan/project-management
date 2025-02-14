<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Project Management App</h1>
        <div id="app">
            <!-- Navigasi Tab -->
            <ul class="nav nav-tabs authTabs" style="display:none;">
                <li class="nav-item">
                    <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login-form">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="register-tab" data-toggle="tab" href="#register-form">Register</a>
                </li>
            </ul>
            
            <div class="tab-content mt-3 authTabs">
                <!-- Form Login -->
                <div id="login-form" class="tab-pane fade show active">
                    <h2>Login</h2>
                    <form id="login">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
                
                <!-- Form Registrasi -->
                <div id="register-form" class="tab-pane fade">
                    <h2>Register</h2>
                    <form id="register">
                        <div class="form-group">
                            <label for="reg-username">Username</label>
                            <input type="text" class="form-control" id="reg-username" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Password</label>
                            <input type="password" class="form-control" id="reg-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                </div>
            </div>

            <!-- Daftar Proyek -->
            <div id="projects" style="display:none;">
                <h2>Projects</h2>

                <button id="add-project" class="btn btn-success" data-toggle="modal" data-target="#projectModal">Add Project</button>
                <button id="logout" class="btn btn-danger ml-2">Logout</button>
                
                <div id="project-container" class="row mt-1"></div>

            </div>

            <div class="modal fade" id="projectModal" tabindex="-1" role="dialog" aria-labelledby="projectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="projectModalLabel">Add Project</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="project-form">
                                <input type="hidden" id="project-id">
                                <div class="form-group">
                                    <label for="project-name">Project Name</label>
                                    <input type="text" class="form-control" id="project-name" required>
                                </div>
                                <div class="form-group">
                                    <label for="project-description">Project Description</label>
                                    <textarea class="form-control" id="project-description" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="save-project">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let token = localStorage.getItem('token');

        if (token) {
            $('.authTabs').hide();
            $('#projects').show();
            loadProjects();
        } else {
            $('.authTabs').show();
            $('#projects').hide();
        }

        // Login
        $('#login').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '/api/login',
                method: 'POST',
                data: {
                    username: $('#username').val(),
                    password: $('#password').val()
                },
                success: function(response) {
                    localStorage.setItem('token', response.token);
                    $('.authTabs').hide();
                    $('#projects').show();
                    loadProjects();
                },
                error: function(xhr) {
                    alert('Login gagal! Periksa kembali username dan password.');
                }
            });
        });

        // Registrasi
        $('#register').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '/api/register',
                method: 'POST',
                data: {
                    username: $('#reg-username').val(),
                    password: $('#reg-password').val()
                },
                success: function(response) {
                    alert('Registration successful! Please login.');
                    $('#register-tab').removeClass('active');
                    $('#login-tab').addClass('active');
                    $('#register-form').removeClass('show active');
                    $('#login-form').addClass('show active');
                },
                error: function(xhr) {
                    let errorMessage = 'Registration failed! ';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage += Object.values(xhr.responseJSON.errors).join(' ');
                    } else {
                        errorMessage += 'Please check your input.';
                    }
                    alert(errorMessage);
                }
            });
        });

        // Load Projects
        function loadProjects() {
            $.ajax({
                url: '/api/projects',
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                success: function(projects) {
                    $('#project-container').empty();
                    projects.forEach(project => {
                        $('#project-container').append(`
                            <div class="col-md-12 mt-   ">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${project.name}</h5>
                                        <p class="card-text">${project.description}</p>
                                        <button class="btn btn-warning edit-project" data-id="${project.id}" data-name="${project.name}" data-description="${project.description}" data-toggle="modal" data-target="#projectModal">Edit</button>
                                        <button class="btn btn-danger delete-project" data-id="${project.id}">Delete</button>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            });
        }

        // Save Project    
        $('#save-project').click(function() {
            const id = $('#project-id').val();
            const name = $('#project-name').val();
            const description = $('#project-description').val();
            if (name && description) {
                const method = id ? 'PUT' : 'POST';
                const url = id ? `/api/projects/${id}` : '/api/projects';

                $.ajax({
                    url: url,
                    method: method,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: {
                        name: name,
                        description: description
                    },
                    success: function() {
                        $('#projectModal').modal('hide');
                        loadProjects();
                    }
                });
            }
        });

        //Reset form ketika tombol add di klik
        $('#add-project').click(function() {
            $('#project-id').val('');
            $('#project-name').val('');           
            $('#project-description').val('');
        });

        //Menampilkan data di form edit
        $(document).on('click', '.edit-project', function() {
            $('#project-id').val($(this).data('id'));
            $('#project-name').val($(this).data('name'));
            $('#project-description').val($(this).data('description'));
        });

        // Hapus Project
        $(document).on('click', '.delete-project', function() {
            const id = $(this).data('id');
            if (confirm('Are you sure you want to delete this project?')) {
                $.ajax({
                    url: `/api/projects/${id}`,
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    success: function() {
                        loadProjects();
                    }
                });
            }
        });

        // Fungsi logout
        $('#logout').click(function() {
            $.ajax({
                url: '/api/logout',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                success: function() {
                    localStorage.removeItem('token'); // Hapus token di klien
                    $('#projects').hide();
                    $('.authTabs').show();
                }
            });
        });



    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
