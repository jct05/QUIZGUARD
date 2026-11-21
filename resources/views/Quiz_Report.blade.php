<!DOCTYPE html>
<html>

<head>
    <title>Quiz Report - DWCC QUIZGUARD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            color: #1f2937;
        }

        .sidebar {
            width: 240px;
            background: #144532;
            color: white;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .logo-q {
            background: white;
            color: #144532;
            font-weight: bold;
            font-size: 20px;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand h3 {
            margin: 0;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .brand small {
            font-size: 8.5px;
            color: #a7c4b8;
            display: block;
        }

        .menu-label {
            font-size: 11px;
            color: #8cb0a2;
            margin: 10px 0 8px 10px;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2ece8;
            text-decoration: none;
            padding: 11px 14px;
            margin: 4px 0;
            border-radius: 8px;
            font-size: 14px;
        }

        .sidebar a:hover,
        .active {
            background: #235c46;
            color: white;
            font-weight: 500;
        }

        .profile-box {
            border-top: 1px solid #235c46;
            padding-top: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            background: white;
            color: #144532;
            font-weight: bold;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: white;
            padding: 12px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-wrap {
            position: relative;
            width: 320px;
        }

        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 10px;
            color: #9ca3af;
            font-size: 14px;
        }

        .search-wrap input {
            width: 100%;
            padding: 8px 12px 8px 35px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            font-size: 13px;
            box-sizing: border-box;
        }

        .top-icons {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar-dark {
            background: #144532;
            color: white;
            font-weight: bold;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .main {
            flex: 1;
            padding: 28px 35px;
            max-width: 1250px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 24px;
        }

        .btn {
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-green {
            background: #144532;
            color: white;
            border: none;
        }

        .btn-white {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: white;
            padding: 18px 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .icon-green {
            background: #ecfdf5;
            color: #144532;
        }

        .icon-yellow {
            background: #fefce8;
            color: #ca8a04;
        }

        .stat-card h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .stat-card small {
            color: #6b7280;
            font-size: 12px;
            display: block;
        }

        .content {
            display: flex;
            gap: 22px;
            align-items: flex-start;
        }

        .box {
            background: white;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .tabs {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 16px;
        }

        .tab {
            padding-bottom: 10px;
            font-size: 13.5px;
            color: #6b7280;
            cursor: pointer;
            font-weight: 500;
        }

        .tab-active {
            color: #144532;
            border-bottom: 2px solid #144532;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 10px 8px;
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 1px solid #f3f4f6;
        }

        td {
            padding: 30px 8px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }

        .chart-area {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 110px;
            padding: 15px 10px 0 10px;
            border-bottom: 1px solid #e5e7eb;
            margin: 15px 0;
            gap: 10px;
        }

        .bar-col {
            flex: 1;
            text-align: center;
        }

        .bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 4px 4px 0 0;
            margin-bottom: 6px;
        }

        .bar-label {
            font-size: 10px;
            color: #9ca3af;
        }

        .dist-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .dist-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div>
            <div class="brand">
                <div class="logo-q">Q</div>
                <div>
                    <h3>DWCC QUIZGUARD</h3>
                    <small>DIVINE WORD COLLEGE OF CALAPAN</small>
                </div>
            </div>
            <div class="menu-label">MENU</div>
            <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="#"><i class="fa-regular fa-folder"></i> My Library</a>
            <a href="#"><i class="fa-solid fa-list-check"></i> Quizzes</a>
            <a href="#" class="active"><i class="fa-solid fa-chart-simple"></i> Reports</a>
            <a href="#"><i class="fa-solid fa-gear"></i> Settings</a>
        </div>
        <div class="profile-box">
            <div class="profile-info">
                <div class="avatar"><i class="fa-regular fa-user"></i></div>
                <div>
                    <strong style="font-size: 13px;">Teacher Name</strong><br>
                    <small style="color: #a7c4b8; font-size: 11px;">Teacher</small>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right-from-bracket" style="color: #a7c4b8; font-size: 14px;"></i>
        </div>
    </div>

    <div class="wrapper">
        <div class="topbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search quizzes, students..." />
            </div>
            <div class="top-icons">
                <i class="fa-regular fa-bell" style="color: #4b5563; font-size: 17px;"></i>
                <div class="avatar-dark"><i class="fa-regular fa-user"></i></div>
            </div>
        </div>

        <div class="main">
            <div class="header">
                <div>
                    <small style="color: #6b7280; font-size: 12.5px;">Reports / Quiz</small>
                    <h2 style="margin: 4px 0; font-size: 24px;">Quiz Results</h2>
                    <small style="color: #9ca3af; font-size: 12px;">Section · No schedule yet</small>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-white"><i class="fa-solid fa-download"></i> Export Excel</button>
                    <button class="btn btn-green"><i class="fa-solid fa-file-arrow-down"></i> Export PDF</button>
                </div>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-chart-column"></i></div>
                    <div>
                        <h2>0%</h2>
                        <small>Average score</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-check"></i></div>
                    <div>
                        <h2>0/0</h2>
                        <small>Submitted</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <h2>0/0</h2>
                        <small>Highest score</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-yellow"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h2>0</h2>
                        <small>Total violations</small>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="box" style="flex: 2.2;">
                    <div class="tabs">
                        <div class="tab tab-active">Results</div>
                        <div class="tab">Violations</div>
                        <div class="tab">Per Question</div>
                    </div>

                    <table>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Score</th>
                            <th>Started</th>
                            <th>Submitted</th>
                            <th>Time</th>
                            <th>Violations</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <td colspan="8">No quiz results available yet.</td>
                        </tr>
                    </table>
                </div>

                <div class="box" style="flex: 1;">
                    <strong style="font-size: 15px;">Score Distribution</strong><br>
                    <small style="color: #6b7280; font-size: 12px;">0 submissions</small>

                    <div class="chart-area">
                        <div class="bar-col">
                            <div class="bar"></div><span class="bar-label">&lt;50</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar"></div><span class="bar-label">50-59</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar"></div><span class="bar-label">60-69</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar"></div><span class="bar-label">70-79</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar"></div><span class="bar-label">80-89</span>
                        </div>
                    </div>

                    <div class="dist-row">
                        <span style="color: #6b7280;">Passing score</span>
                        <strong>75%</strong>
                    </div>
                    <div class="dist-row">
                        <span style="color: #6b7280;">Passing rate</span>
                        <strong>0%</strong>
                    </div>
                    <div class="dist-row">
                        <span style="color: #6b7280;">Lowest score</span>
                        <strong>0/0</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>