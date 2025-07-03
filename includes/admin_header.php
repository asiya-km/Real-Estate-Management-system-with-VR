<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-card .title {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .bg-blue { background-color: #4361ee; }
        .bg-green { background-color: #4caf50; }
        .bg-purple { background-color: #7209b7; }
        .bg-orange { background-color: #f9844a; }
        .bg-red { background-color: #e63946; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 p-0">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 p-4">