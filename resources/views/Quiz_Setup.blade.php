<!DOCTYPE html>
<html>

<head>
    <title>Quiz_SETUP</title>
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
        .grid {
            display: flex;
            gap: 20px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .col {
            flex: 1;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .check-item {
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h3>DWCC QUIZGUARD</h3>
        <hr>
        <a href="#">Dashboard</a>
        <a href="#">My Library</a>
        <a href="#" class="active">Quizzes</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <small style="color: gray;">Quizzes / Create</small>
                <h2 style="margin: 5px 0;">Create Quiz</h2>
            </div>
            <div>
                <button class="btn btn-white">Save as Draft</button>
                <button class="btn btn-green">✓ Publish Quiz</button>
            </div>
        </div>

        <div class="grid">
            <div class="box" style="flex: 2;">
                <h3>Quiz Details</h3>
                <hr><br>

                <div style="margin-bottom: 15px;">
                    <label>Quiz Title</label>
                    <input type="text" placeholder="Enter quiz title" />
                </div>

                <div class="row">
                    <div class="col">
                        <label>Question Bank</label>
                        <select>
                            <option>Select question bank...</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Section</label>
                        <select>
                            <option>Select section...</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Quiz Type</label>
                        <select>
                            <option>Multiple Choice</option>
                            <option>Identification</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Number of Questions</label>
                        <input type="number" placeholder="0" />
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Time Limit (minutes)</label>
                        <input type="number" placeholder="0" />
                    </div>
                    <div class="col">
                        <label>Passing Score (%)</label>
                        <input type="number" placeholder="75" />
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Opens At</label>
                        <input type="datetime-local" />
                    </div>
                    <div class="col">
                        <label>Closes At</label>
                        <input type="datetime-local" />
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Attempts Allowed</label>
                        <input type="number" value="1" />
                    </div>
                    <div class="col">
                        <label>Grading Method</label>
                        <select>
                            <option>Highest score</option>
                            <option>Latest score</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="flex: 1;">
                <div class="box">
                    <h3>Randomization</h3>
                    <hr><br>
                    <div class="check-item"><input type="checkbox" checked> Shuffle questions</div>
                    <div class="check-item"><input type="checkbox" checked> Shuffle choices</div>
                </div>

                <div class="box">
                    <h3>Anti-Cheat Settings</h3>
                    <hr><br>
                    <div class="check-item"><input type="checkbox" checked> Detect tab switching</div>
                    <div class="check-item"><input type="checkbox" checked> Detect window switching</div>
                    <div class="check-item"><input type="checkbox" checked> Detect exit from full-screen</div>
                    <div class="check-item"><input type="checkbox" checked> Detect third-party app use</div>
                    <br>
                    <label>Allowed Violations</label>
                    <input type="number" value="3" />
                </div>
            </div>
        </div>
    </div>

</body>

</html>