<!DOCTYPE html>
<html>

<head>
    <title>My Library</title>
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
            margin-bottom: 18px;
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
        .tabs {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 22px;
        }
        .tab {
            padding-bottom: 10px;
            font-size: 13.5px;
            color: #6b7280;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tab-active {
            color: #144532;
            border-bottom: 2px solid #144532;
            font-weight: 600;
        }

        .pill {
            background: #f3f4f6;
            color: #4b5563;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 11px;
        }

        .sec-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .view-all {
            color: #144532;
            font-size: 12.5px;
            text-decoration: none;
            font-weight: 600;
        }

        .folders {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        .folder-card {
            background: white;
            padding: 18px 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .folder-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .f-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .f-green {
            background: #ecfdf5;
            color: #144532;
        }

        .f-blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .f-yellow {
            background: #fefce8;
            color: #ca8a04;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filter-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .filter-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lib-search {
            position: relative;
            width: 260px;
        }

        .lib-search i {
            position: absolute;
            left: 11px;
            top: 10px;
            color: #9ca3af;
            font-size: 13px;
        }

        .lib-search input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            box-sizing: border-box;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            color: #374151;
            background: white;
        }

        .icon-btn {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #4b5563;
            cursor: pointer;
        }

        .icon-btn-active {
            background: #ecfdf5;
            color: #144532;
            border-color: #bbf7d0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 10px 8px;
            font-size: 11.5px;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 1px solid #f3f4f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="#" class="active"><i class="fa-regular fa-folder"></i> My Library</a>
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
                    <small style="color: #6b7280; font-size: 11.5px; letter-spacing: 0.5px; font-weight: 600;">MY
                        WORKSPACE</small>
                    <h2 style="margin: 4px 0 0 0; font-size: 24px;">My library</h2>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-white"><i class="fa-solid fa-folder-plus"></i> New folder</button>
                    <button class="btn btn-green">+ Create resource</button>
                </div>
            </div>

            <div class="tabs">
                <div class="tab tab-active">Created by me <span class="pill">0</span></div>
                <div class="tab">Shared with me <span class="pill">0</span></div>
                <div class="tab">Bookmarked <span class="pill">0</span></div>
            </div>

            <div class="sec-head">
                <strong style="font-size: 14px;">Folders</strong>
                <a href="#" class="view-all">View all folders →</a>
            </div>

            <div class="folders">
                <div class="folder-card">
                    <div class="folder-left">
                        <div class="f-icon f-green"><i class="fa-regular fa-folder"></i></div>
                        <div>
                            <strong style="font-size: 13.5px;">Lesson Materials</strong><br>
                            <small style="color: #6b7280; font-size: 12px;">0 resources</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-ellipsis" style="color: #9ca3af;"></i>
                </div>

                <div class="folder-card">
                    <div class="folder-left">
                        <div class="f-icon f-blue"><i class="fa-regular fa-folder"></i></div>
                        <div>
                            <strong style="font-size: 13.5px;">Quizzes &amp; Assessments</strong><br>
                            <small style="color: #6b7280; font-size: 12px;">0 resources</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-ellipsis" style="color: #9ca3af;"></i>
                </div>

                <div class="folder-card">
                    <div class="folder-left">
                        <div class="f-icon f-yellow"><i class="fa-regular fa-folder"></i></div>
                        <div>
                            <strong style="font-size: 13.5px;">Practice Assessments</strong><br>
                            <small style="color: #6b7280; font-size: 12px;">0 resources</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-ellipsis" style="color: #9ca3af;"></i>
                </div>
            </div>

            <div class="box">
                <div class="filter-bar">
                    <div class="filter-left">
                        <div class="lib-search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Search in my library" />
                        </div>
                        <select>
                            <option>All types</option>
                        </select>
                        <select>
                            <option>All subjects</option>
                        </select>
                    </div>
                    <div class="filter-right">
                        <select>
                            <option>⇅ Last modified</option>
                        </select>
                        <button class="icon-btn icon-btn-active"><i class="fa-solid fa-list"></i></button>
                        <button class="icon-btn"><i class="fa-solid fa-grip"></i></button>
                    </div>
                </div>

                <table>
                    <tr>
                        <th>Resource (0)</th>
                        <th>Visibility</th>
                        <th>Last Modified</th>
                        <th style="width: 40px;"></th>
                    </tr>
                    <tr>
                        <td colspan="4">No resources found in your library yet.</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>