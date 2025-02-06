<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lib/bootstrap.min.css">
    <link rel="stylesheet" href="lib/docs.css">
    <script src="lib/bootstrap.bundle.min.js"></script>
    <style>
        #logout {
            margin-left: auto;
        }
        .nav-link {
            margin-right: 15px;
            color: black; 
        }
        .nav-logout {
            margin-right: 10px; 
        }
        nav {
            background-color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin.php" style="color: black;">CAFE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="nav-link active" aria-current="page" href="employees.php" style="color: black;">Employees</a>
            <a class="nav-link active" aria-current="page" href="stock.php" style="color: black;">Stock</a>
            <a class="nav-link active" aria-current="page" href="supplier.php" style="color: black;">Supplier</a>
            <a class="nav-link active" aria-current="page" href="invoice.php" style="color: black;">Invoice</a>
            <a class="nav-link nav-logout" id="logout" aria-current="page" href="logout.php" style="color: black;">LOG OUT</a>
        </div>
    </nav>
</body>
</html>
