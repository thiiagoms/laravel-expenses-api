<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
        }
        .expense-item {
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .expense-item p {
            margin: 5px 0;
        }
        .expense-item span {
            font-weight: bold;
            color: #343a40;
        }
        p {
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="my-4">Expenses Report</h2>
        <p>Dear {{ $expense->user->name}},</p>
        <p>Please find below the details of the expenses incurred:</p>
        <div class="expense-item">
            <p>
                <span>Description:</span>
                {{ $expense->description }}
            </p>
            <p>
                <span>Price:</span>
                {{ $expense->price }}
            </p>
            <p>
                <span>Date:</span>
                {{ $expense->date }}
            </p>
        </div>
    </div>
</body>
</html>
