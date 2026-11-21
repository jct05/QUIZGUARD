<!DOCTYPE html>
<html>

<head>
    <title>Quiz Report</title>
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
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            border: 1px solid #ddd;
        }
        .content {
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
            margin-top: 15px;
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
        <a href="#">Dashboard</a>
        <a href="#">My Library</a>
        <a href="#">Quizzes</a>
        <a href="#" class="active">Reports</a>
        <a href="#">Settings</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <small style="color: gray;">Reports</small>
                <h2 style="margin: 5px 0;">Quiz Results</h2>
            </div>
            <div>
                <button class="btn btn-white">Export Excel</button>
                <button class="btn btn-green">Export PDF</button>
            </div>
        </div>

        <div class="stats">
            <div class="card"><small>Average Score</small>
                <h2>0%</h2>
            </div>
            <div class="card"><small>Submitted</small>
                <h2>0 / 0</h2>
            </div>
            <div class="card"><small>Highest Score</small>
                <h2>0 / 0</h2>
            </div>
            <div class="card"><small>Total Violations</small>
                <h2>0</h2>
            </div>
        </div>

        <div class="content">
            <div class="box" style="flex: 2;">
                <p><strong>Results</strong> | Violations | Per Question</p>
                <table>
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Score</th>
                        <th>Submitted</th>
                        <th>Violations</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: center; color: gray;">No results available yet.</td>
                    </tr>
                </table>
            </div>

            <div class="box" style="flex: 1;">
                <h3>Score Distribution</h3>
                <hr>
                <p>Passing score: <strong>75%</strong></p>
                <p>Passing rate: <strong>0%</strong></p>
                <p>Lowest score: <strong>0</strong></p>
            </div>
        </div>
    </div>

</body>

</html>