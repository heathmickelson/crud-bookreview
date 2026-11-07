<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booked & Reviewed | Home</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

            <a class="navbar-brand" href="index.php">
                Booked & Reviewed
            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mt-3">
                    <li>
                        <form action="index.php" method="GET" class="mb-4">
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    name="search" 
                                    class="form-control" 
                                    placeholder="Search books by title..."
                                >

                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>
                            </div>
                        </form>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books/index.php">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reviews/index.php">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users/index.php">Login</a>
                    </li>
                </ul>
            </div>

            

        </div>
    </nav>