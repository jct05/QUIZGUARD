<!DOCTYPE html>
<html>

<head>
    <title>Prof Dashboard - DWCC QUIZGUARD</title>
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
            align-items: center;
            margin-bottom: 24px;
        }

        .title-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .badge-active {
            background: #dcfce7;
            color: #15803d;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 500;
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

        .box-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .view-all {
            color: #144532;
            font-size: 12.5px;
            text-decoration: none;
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
            padding: 35px 8px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
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
            <a href="#" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="#"><i class="fa-regular fa-folder"></i> My Library</a>
            <a href="#"><i class="fa-solid fa-list-check"></i> Quizzes</a>
            <a href="#"><i class="fa-solid fa-chart-simple"></i> Reports</a>
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
                    <div class="title-row">
                        <h2 style="margin: 0; font-size: 24px;">Hello, Teacher 👋</h2>
                        <span class="badge-active">● Monitoring is active</span>
                    </div>
                    <small style="color: #6b7280; font-size: 13px;">Monitor your quizzes today</small>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-white"><i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Word
                        File</button>
                    <button class="btn btn-green">+ Create Quiz</button>
                </div>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-check"></i></div>
                    <div>
                        <h2>0</h2>
                        <small>Active quizzes</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-regular fa-folder"></i></div>
                    <div>
                        <h2>0</h2>
                        <small>Library</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h2>0</h2>
                        <small>Submissions today</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-yellow"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h2>0</h2>
                        <small>Violations today</small>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="box" style="flex: 2.1;">
                    <div class="box-head">
                        <div>
                            <strong style="font-size: 15.5px;">My Quizzes</strong><br>
                            <small style="color: #6b7280; font-size: 12px;">Quizzes you have published</small>
                        </div>
                        <a href="#" class="view-all">View all →</a>
                    </div>

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
                            <td colspan="6">No quizzes published yet.</td>
                        </tr>
                    </table>
                </div>

                <div class="box" style="flex: 1;">
                    <div class="box-head">
                        <div>
                            <strong style="font-size: 15.5px;">Recent Violations</strong><br>
                            <small style="color: #6b7280; font-size: 12px;">Latest across your quizzes</small>
                        </div>
                        <a href="#" class="view-all">View all →</a>
                    </div>

                    <p style="text-align: center; color: #9ca3af; font-size: 13px; padding: 35px 0;">No recent
                        violations yet.</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>