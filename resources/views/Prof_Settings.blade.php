<!DOCTYPE html>
<html>

<head>
    <title>Settings - DWCC QUIZGUARD</title>
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

        .btn {
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
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

        .content {
            display: flex;
            gap: 22px;
            align-items: flex-start;
        }

        .col-left {
            flex: 1.4;
        }

        .col-right {
            flex: 1;
        }

        .box {
            background: white;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 22px;
        }

        .box-title {
            margin: 0 0 3px 0;
            font-size: 16px;
            font-weight: 600;
        }

        .box-sub {
            color: #6b7280;
            font-size: 12.5px;
            display: block;
            margin-bottom: 18px;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 18px;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 18px;
        }

        .avatar-lg {
            background: #144532;
            color: white;
            font-weight: bold;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        label {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-field,
        select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1f2937;
            box-sizing: border-box;
            background: white;
        }

        .input-disabled {
            background: #f9fafb;
            color: #6b7280;
        }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 12px;
        }

        .stepper button {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #4b5563;
        }

        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 22px;
            flex-shrink: 0;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            border-radius: 22px;
            transition: .2s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: .2s;
        }

        input:checked+.slider {
            background-color: #144532;
        }

        input:checked+.slider:before {
            transform: translateX(16px);
        }

        .info-banner {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sec-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .sec-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .sec-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sec-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #4b5563;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
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
            <a href="#"><i class="fa-solid fa-chart-simple"></i> Reports</a>
            <a href="#" class="active"><i class="fa-solid fa-gear"></i> Settings</a>
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
                    <h2 style="margin: 0; font-size: 24px;">Settings</h2>
                    <small style="color: #6b7280; font-size: 13px;">Manage your profile, report preferences, and quiz
                        defaults.</small>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-white">Cancel</button>
                    <button class="btn btn-green">✓ Save Changes</button>
                </div>
            </div>

            <div class="content">
                <div class="col-left">
                    <div class="box">
                        <h3 class="box-title">Profile</h3>
                        <span class="box-sub">Your DWCC account details</span>

                        <div class="profile-header">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div class="avatar-lg"><i class="fa-regular fa-user"></i></div>
                                <div>
                                    <strong style="font-size: 15px;">Teacher Name</strong><br>
                                    <small style="color: #6b7280;">Teacher · Department</small>
                                </div>
                            </div>
                            <button class="btn btn-white">Change Photo</button>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Full name</label>
                                <input type="text" class="input-field" placeholder="Enter full name" />
                            </div>
                            <div>
                                <label>Employee ID</label>
                                <input type="text" class="input-field input-disabled" placeholder="Employee ID"
                                    readonly />
                            </div>
                            <div>
                                <label>DWCC email</label>
                                <input type="text" class="input-field input-disabled" placeholder="email@dwcc.edu.ph"
                                    readonly />
                            </div>
                            <div>
                                <label>Department</label>
                                <select>
                                    <option value="">Select department</option>
                                    <option>IT Department</option>
                                    <option>Computer Science</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <h3 class="box-title">Quiz Defaults</h3>
                        <span class="box-sub">Applied automatically when you create a new quiz</span>

                        <div class="grid-2">
                            <div>
                                <label>Default time limit</label>
                                <input type="text" class="input-field" placeholder="0 minutes" />
                            </div>
                            <div>
                                <label>Default attempts</label>
                                <div class="stepper">
                                    <button>-</button>
                                    <span style="font-size: 13.5px; font-weight: 500;">0</span>
                                    <button>+</button>
                                </div>
                            </div>
                            <div>
                                <label>Default allowed violations</label>
                                <div class="stepper">
                                    <button>-</button>
                                    <span style="font-size: 13.5px; font-weight: 500;">0</span>
                                    <button>+</button>
                                </div>
                            </div>
                            <div>
                                <label>Default grading method</label>
                                <select>
                                    <option value="">Select grading method</option>
                                    <option>Highest score</option>
                                    <option>Latest score</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2" style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #f3f4f6;">
                            <div class="toggle-row">
                                <div>
                                    <strong style="font-size: 13px;">Shuffle questions</strong><br>
                                    <small style="color: #6b7280; font-size: 11.5px;">On for every new quiz</small>
                                </div>
                                <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <strong style="font-size: 13px;">Shuffle choices</strong><br>
                                    <small style="color: #6b7280; font-size: 11.5px;">On for every new quiz</small>
                                </div>
                                <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-right">
                    <div class="box">
                        <h3 class="box-title">Report Preferences</h3>
                        <span class="box-sub">How your quiz reports are exported</span>

                        <label>Default export format</label>
                        <select style="margin-bottom: 14px;">
                            <option value="">Select export format</option>
                            <option>Excel (.xlsx)</option>
                            <option>PDF (.pdf)</option>
                        </select>

                        <div class="toggle-row" style="border-top: 1px solid #f3f4f6;">
                            <div>
                                <strong style="font-size: 13px;">Include violation log</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Timestamped violations for each
                                    student</small>
                            </div>
                            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                        </div>

                        <div class="toggle-row" style="border-top: 1px solid #f3f4f6;">
                            <div>
                                <strong style="font-size: 13px;">Include per-question results</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Correct answers and time per
                                    question</small>
                            </div>
                            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                        </div>

                        <div class="info-banner">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Download reports from the <strong>Reports</strong> page</span>
                        </div>
                    </div>

                    <div class="box">
                        <h3 class="box-title">Security</h3>
                        <span class="box-sub">Password and sign-in</span>

                        <div class="sec-row">
                            <div class="sec-left">
                                <div class="sec-icon"><i class="fa-solid fa-lock"></i></div>
                                <div>
                                    <strong style="font-size: 13px;">Password</strong><br>
                                    <small style="color: #6b7280; font-size: 11.5px;">Not set</small>
                                </div>
                            </div>
                            <button class="btn btn-white">Change</button>
                        </div>

                        <div class="sec-row">
                            <div class="sec-left">
                                <div class="sec-icon"><i class="fa-brands fa-google"></i></div>
                                <div>
                                    <strong style="font-size: 13px;">Google account</strong><br>
                                    <small style="color: #6b7280; font-size: 11.5px;">No account linked</small>
                                </div>
                            </div>
                            <span class="badge-gray">Not connected</span>
                        </div>

                        <div class="sec-row">
                            <div class="sec-left">
                                <div class="sec-icon"><i class="fa-solid fa-display"></i></div>
                                <div>
                                    <strong style="font-size: 13px;">Active sessions</strong><br>
                                    <small style="color: #6b7280; font-size: 11.5px;">0 devices signed in</small>
                                </div>
                            </div>
                            <button class="btn btn-white">Sign out all</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>