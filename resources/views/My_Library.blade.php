<!DOCTYPE html>
<html>

<head>
    <title>My Library</title>
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
            margin-bottom: 15px;
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
        .tabs {
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .tabs span {
            cursor: pointer;
            color: gray;
        }
        .tabs .tab-active {
            color: #154734;
            font-weight: bold;
        }
        .folders {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .folder-card {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            flex: 1;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .search-bar input,
        .search-bar select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-bar input {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3>DWCC QUIZGUARD</h3>
        <hr>
        <a href="#">Dashboard</a>
        <a href="#" class="active">My Library</a>
        <a href="#">Quizzes</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <small style="color: gray;">MY WORKSPACE</small>
                <h2 style="margin: 5px 0;">My library</h2>
            </div>
            <div>
                <button class="btn btn-white">New Folder</button>
                <button class="btn btn-green">+ Create resource</button>
            </div>
        </div>

        <div class="tabs">
            <span class="tab-active">Created by me (0)</span>
            <span>Shared with me (0)</span>
            <span>Bookmarked (0)</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <strong>Folders</strong>
            <a href="#" style="color: #154734; text-decoration: none; font-size: 14px;">View all folders →</a>
        </div>
        <div class="folders">
            <div class="folder-card">
                <div>📁</div>
                <div>
                    <strong>Lesson Materials</strong><br>
                    <small style="color: gray;">0 items</small>
                </div>
            </div>
            <div class="folder-card">
                <div>📁</div>
                <div>
                    <strong>Quizzes & Assessments</strong><br>
                    <small style="color: gray;">0 items</small>
                </div>
            </div>
            <div class="folder-card">
                <div>📁</div>
                <div>
                    <strong>Practice Assessments</strong><br>
                    <small style="color: gray;">0 items</small>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="search-bar">
                <input type="text" placeholder="Search in my library..." />
                <select>
                    <option>All types</option>
                </select>
                <select>
                    <option>All subjects</option>
                </select>
                <select>
                    <option>Last modified</option>
                </select>
            </div>

            <table>
                <tr>
                    <th>Resource (0)</th>
                    <th>Visibility</th>
                    <th>Last Modified</th>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; color: gray; padding: 30px;">
                        No resources found in your library yet.
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>