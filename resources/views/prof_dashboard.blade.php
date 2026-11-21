<!DOCTYPE html>
<html>

<head>
    <title>Prof Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
        }
        .sidebar {
            width: 220px;
            background: #154734;
            color: white;
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a:hover,
        .active {
            background: #1f6148;
        }
        .main {
            flex: 1;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-green {
            background: #154734;
            color: white;
        }
        .btn-white {
            background: white;
            border: 1px solid #ccc;
        }
        .cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            flex: 1;
            border: 1px solid #ddd;
        }
        .tables {
            display: flex;
            gap: 20px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3>DWCC QUIZGUARD</h3>
        <hr>
        <a href="#" class="active">Dashboard</a>
        <a href="#">My Library</a>
        <a href="#">Quizzes</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h2>Teacher Dashboard</h2>
                <p>Monitor your quizzes today</p>
            </div>
            <div>
                <button class="btn btn-white">Upload Word File</button>
                <button class="btn btn-green">+ Create Quiz</button>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h2>0</h2>
                <p>Active quizzes</p>
            </div>
            <div class="card">
                <h2>0</h2>
                <p>Library</p>
            </div>
            <div class="card">
                <h2>0</h2>
                <p>Submissions today</p>
            </div>
            <div class="card">
                <h2>0</h2>
                <p>Violations today</p>
            </div>
        </div>

        <div class="tables">
            <div class="box" style="flex: 2;">
                <h3>My Quizzes</h3>
                <table>
                    <tr>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Items</th>
                        <th>Closes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: center; color: gray;">No quizzes yet.</td>
                    </tr>
                </table>
            </div>

            <div class="box" style="flex: 1;">
                <h3>Recent Violations</h3>
                <p style="color: gray; text-align: center;">No violations yet.</p>
            </div>
        </div>
    </div>

</body>

</html>