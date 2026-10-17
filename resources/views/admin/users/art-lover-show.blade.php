
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Lover Details | Artfy Admin</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 35px 20px;
            background: #f8f6f0;
            color: #30382b;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
        }

        .back {
            display: inline-block;
            margin-bottom: 25px;
            color: #596440;
            text-decoration: none;
            font-weight: 600;
        }

        .back:hover {
            text-decoration: underline;
        }

        .card {
            background: #fff;
            border: 1px solid #e8e5dc;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 30px rgba(40, 45, 30, 0.05);
        }

        .eyebrow {
            color: #77805f;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin: 10px 0;
            font-family: Georgia, serif;
            font-size: 32px;
        }

        .description {
            color: #77796f;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 22px 0;
            border-bottom: 1px solid #eeeae1;
        }

        .avatar {
            width: 76px;
            height: 76px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8eadf;
            color: #596440;
            font-size: 28px;
            font-weight: bold;
        }

        .profile h2 {
            margin: 0 0 7px;
            font-size: 22px;
        }

        .profile p {
            margin: 0;
            color: #77796f;
            overflow-wrap: anywhere;
        }

        .details {
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 17px 0;
            border-bottom: 1px solid #eeeae1;
        }

        .detail-label {
            color: #77796f;
        }

        .detail-value {
            text-align: right;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .role {
            display: inline-block;
            background: #edf0e5;
            color: #596440;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .footer {
            margin-top: 28px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background: #596440;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .button:hover {
            background: #424c31;
        }

        @media (max-width: 550px) {
            body {
                padding: 20px 12px;
            }

            .card {
                padding: 22px 18px;
            }

            .detail-row {
                flex-direction: column;
                gap: 7px;
            }

            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <main class="container">
        <a class="back" href="{{ route('admin.art-lovers.index') }}">
            ← Back to All Art Lovers
        </a>

        <section class="card">
            <div class="eyebrow">Artfy Administration</div>

            <h1>Art Lover Details</h1>

            <p class="description">
                View this member's account information and registration details.
            </p>

            <div class="profile">
                <div class="avatar">
                    {{ strtoupper(substr($artLover->name ?? 'U', 0, 1)) }}
                </div>

                <div>
                    <h2>{{ $artLover->name }}</h2>
                    <p>{{ $artLover->email }}</p>
                </div>
            </div>

            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">User ID</span>
                    <span class="detail-value">#{{ $artLover->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value">{{ $artLover->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email Address</span>
                    <span class="detail-value">{{ $artLover->email }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Account Role</span>
                    <span class="detail-value">
                        <span class="role">Art Lover</span>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email Verification</span>
                    <span class="detail-value">
                        {{ $artLover->email_verified_at ? 'Verified' : 'Not verified' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Registered On</span>
                    <span class="detail-value">
                        {{ $artLover->created_at
                            ? $artLover->created_at->format('d M Y, h:i A')
                            : 'Not available' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Last Updated</span>
                    <span class="detail-value">
                        {{ $artLover->updated_at
                            ? $artLover->updated_at->format('d M Y, h:i A')
                            : 'Not available' }}
                    </span>
                </div>
            </div>

            <div class="footer">
                <a class="button" href="{{ route('admin.art-lovers.index') }}">
                    Back to Art Lovers
                </a>
            </div>
        </section>
    </main>
</body>
</html>
