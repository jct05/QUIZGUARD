<!DOCTYPE html>
<html>

<head>
    <title>Create Quiz - DWCC QUIZGUARD</title>
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

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 16px;
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

        .type-group {
            display: flex;
            gap: 8px;
        }

        .type-btn {
            flex: 1;
            padding: 9px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
        }

        .type-active {
            background: #144532;
            color: white;
            border: 1px solid #144532;
        }

        .type-inactive {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
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
            padding: 12px 0;
            border-top: 1px solid #f3f4f6;
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

        .shield-banner {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .check-item input {
            margin-top: 3px;
            accent-color: #144532;
            width: 15px;
            height: 15px;
        }

        .allowed-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
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
            <a href="#" class="active"><i class="fa-solid fa-list-check"></i> Quizzes</a>
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
                    <small style="color: #6b7280; font-size: 12.5px;">Quizzes / Create</small>
                    <h2 style="margin: 4px 0; font-size: 24px;">Create Quiz</h2>
                    <small style="color: #6b7280; font-size: 13px;">Set up a monitored quiz from one of your question
                        banks.</small>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-white">Save as Draft</button>
                    <button class="btn btn-green">✓ Publish Quiz</button>
                </div>
            </div>

            <div class="content">
                <div class="col-left">
                    <div class="box">
                        <h3 class="box-title">Quiz Details</h3>
                        <span class="box-sub">Basic information and schedule</span>

                        <div>
                            <label>Quiz title</label>
                            <input type="text" class="input-field" placeholder="Enter quiz title" />
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Question bank</label>
                                <select>
                                    <option value="">Select question bank...</option>
                                </select>
                            </div>
                            <div>
                                <label>Section</label>
                                <select>
                                    <option value="">Select section...</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Quiz type</label>
                                <div class="type-group">
                                    <button type="button" class="type-btn type-active">Multiple Choice</button>
                                    <button type="button" class="type-btn type-inactive">Identification</button>
                                </div>
                            </div>
                            <div>
                                <label>Number of questions</label>
                                <input type="number" class="input-field" placeholder="0" />
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Time limit</label>
                                <input type="text" class="input-field" placeholder="0 minutes" />
                            </div>
                            <div>
                                <label>Passing score</label>
                                <input type="text" class="input-field" placeholder="75%" />
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Opens at</label>
                                <input type="datetime-local" class="input-field" />
                            </div>
                            <div>
                                <label>Closes at</label>
                                <input type="datetime-local" class="input-field" />
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Attempts allowed</label>
                                <div class="stepper">
                                    <button type="button">-</button>
                                    <span style="font-size: 13.5px; font-weight: 500;">0</span>
                                    <button type="button">+</button>
                                </div>
                            </div>
                            <div>
                                <label>Grading method</label>
                                <select>
                                    <option value="">Select grading method</option>
                                    <option>Highest score</option>
                                    <option>Latest score</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-right">
                    <div class="box">
                        <h3 class="box-title">Randomization</h3>
                        <span class="box-sub">Give students different versions</span>

                        <div class="toggle-row">
                            <div>
                                <strong style="font-size: 13px;">Shuffle questions</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Randomize question order per
                                    student</small>
                            </div>
                            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                        </div>

                        <div class="toggle-row">
                            <div>
                                <strong style="font-size: 13px;">Shuffle choices</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Randomize the A–D order of
                                    choices</small>
                            </div>
                            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                        </div>
                    </div>

                    <div class="box">
                        <h3 class="box-title">Anti-Cheat Settings</h3>
                        <span class="box-sub">What QuizGuard records during the quiz</span>

                        <div class="shield-banner">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Anti-cheat monitoring enabled</span>
                        </div>

                        <div class="check-item">
                            <input type="checkbox" checked />
                            <div>
                                <strong style="font-size: 13px;">Detect tab switching</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Logs when the student opens another
                                    tab</small>
                            </div>
                        </div>

                        <div class="check-item">
                            <input type="checkbox" checked />
                            <div>
                                <strong style="font-size: 13px;">Detect window switching</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Logs when focus moves to another
                                    window</small>
                            </div>
                        </div>

                        <div class="check-item">
                            <input type="checkbox" checked />
                            <div>
                                <strong style="font-size: 13px;">Detect exit from full-screen</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Logs when full-screen mode is
                                    exited</small>
                            </div>
                        </div>

                        <div class="check-item">
                            <input type="checkbox" checked />
                            <div>
                                <strong style="font-size: 13px;">Detect third-party app use</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Logs apps opened outside the
                                    browser</small>
                            </div>
                        </div>

                        <div class="allowed-row">
                            <div>
                                <strong style="font-size: 13px;">Allowed violations</strong><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Quiz auto-submits after the limit is
                                    reached</small>
                            </div>
                            <div class="stepper" style="width: 95px;">
                                <button type="button">-</button>
                                <span style="font-size: 13.5px; font-weight: 500;">0</span>
                                <button type="button">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>