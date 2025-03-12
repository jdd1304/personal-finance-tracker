<?php
session_start();
include('config.php');  


function get_stock_price($symbol) {
    $api_key = "HOX4HGRYXHRGRY4A";
    $url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=$symbol&interval=5min&apikey=$api_key"; // Alpha Vantage API URL for intraday data

    // Fetch data from the API
    $response = file_get_contents($url);

    if ($response === FALSE) {
        return null; // Return null if API request fails
    }

    $data = json_decode($response, true);
    
 
    if (isset($data['Time Series (5min)'])) {
        // Get the latest available price from the time series data
        $latest_time = key($data['Time Series (5min)']); // Get the most recent time key
        $latest_data = $data['Time Series (5min)'][$latest_time];
        
        // Get the latest price
        $latest_price = $latest_data['4. close']; 
        
        return $latest_price; // Return the stock price
    } else {
        return null; 
    }
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's stocks from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM stocks WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);


if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container {
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
        .modal-header, .modal-footer {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mt-4">Your Stock Portfolio</h1>

    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

 
    <div class="text-center mt-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addStockModal">Add Stock</button>
    </div>


    <div class="table-container">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Stock Name</th>
                    <th>Purchase Price</th>
                    <th>Quantity</th>
                    <th>Total Investment</th>
                    <th>Current Price</th>
                    <th>Profit/Loss</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $stock_name = $row['stock_name'];
                    $purchase_price = $row['purchase_price'];
                    $quantity = $row['quantity'];
                    $purchase_date = $row['purchase_date'];

                 
                    $current_price = get_stock_price($stock_name);
                    if ($current_price !== null) {
                        $total_investment = $purchase_price * $quantity;
                        $profit_loss = ($current_price - $purchase_price) * $quantity;

                      
                        echo "<tr>
                            <td>$stock_name</td>
                            <td>$purchase_price</td>
                            <td>$quantity</td>
                            <td>$total_investment</td>
                            <td>$current_price</td>
                            <td>$profit_loss</td>
                        </tr>";
                        $row_count++;
                    } else {
                       
                        echo "<tr>
                            <td>$stock_name</td>
                            <td>$purchase_price</td>
                            <td>$quantity</td>
                            <td colspan='3'>Price Not Available</td>
                        </tr>";
                        $row_count++;
                    }
                }
                
                
                if ($row_count == 0) {
                    echo "<tr><td colspan='6'>No stocks in your portfolio. Add some stocks to track!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<div class="modal" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="add_stock.php" method="POST">
                    <div class="form-group">
                        <label for="stock_name">Stock Name</label>
                        <input type="text" class="form-control" id="stock_name" name="stock_name" required>
                    </div>
                    <div class="form-group">
                        <label for="purchase_price">Purchase Price</label>
                        <input type="number" class="form-control" id="purchase_price" name="purchase_price" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
