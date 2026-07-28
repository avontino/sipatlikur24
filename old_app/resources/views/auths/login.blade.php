<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SINALA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/adminlte/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f4f6f9;
    }

    .background-image {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -1;
    }

    .login-box {
      width: 90%;
      max-width: 360px;
      padding: 20px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
    }

    .login-logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .login-logo img {
      width: 100px;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .login-logo h1 {
      font-family: 'Source Sans Pro', sans-serif;
      color: #0056b3;
      margin: 5px 0;
      font-size: 36px;
      font-weight: bold;
    }

    .login-logo h2 {
      font-family: 'Source Sans Pro', sans-serif;
      color: #003366;
      margin: 0;
      font-size: 18px;
      font-weight: normal;
    }

    .login-card-body {
      padding: 20px;
    }

    .input-group {
      margin-bottom: 15px;
    }

    .form-control {
      border: 1px solid #0056b3;
      border-radius: 25px;
      padding: 10px 20px;
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
      border-color: #003366;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
      outline: none;
    }

    .input-group-text {
      background-color: #0056b3;
      border: none;
      color: #fff;
      border-top-right-radius: 25px;
      border-bottom-right-radius: 25px;
    }

    .btn-primary {
      background-color: #0056b3;
      border-color: #004494;
      border-radius: 25px;
      padding: 10px;
      font-size: 16px;
    }

    .btn-primary:hover {
      background-color: #004494;
      border-color: #003366;
    }

    .btn-flat {
      border-radius: 25px;
    }
  </style>
</head>
<body>
  <img src="{{ asset('adminlte/img/background.png') }}" alt="Background Image" class="background-image">
  
  <div class="login-box">
    <div class="login-logo">
      <img src="{{ asset('adminlte/img/user2.png') }}" alt="Logo">
      <h1>SINALA</h1>
      <h2>SMAN TARUNA NALA MALANG</h2>
    </div>
    
    <div class="card">
      <div class="card-body login-card-body">
        <form id="loginForm" action="{{ url('/postlogin') }}" method="post">
          {{ csrf_field() }}
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <div class="input-group-append input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
          <div class="row">
            <div class="col-8"></div>
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
            </div>
          </div>
        </form>

        <p class="mb-1 mt-3 text-center">
          <a href="#">Lupa password? Silahkan menghubungi Tim IT</a>
        </p>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
          type: 'POST',
          url: $(this).attr('action'),
          data: $(this).serialize(),
          success: function(response) {
            window.location.href = '/dashboard'; // Redirect on successful login
          },
          error: function(xhr) {
            if (xhr.status === 401) {
              Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: 'Username or Password is incorrect',
              });
            }
          }
        });
      });
    });
  </script>
</body>
</html>
