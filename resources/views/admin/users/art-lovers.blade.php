<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>All Art Lovers | Artfy Admin</title>

    <style>
        :root {
            --cream: #f5f3e8;
            --olive: #55613a;
            --dark-olive: #414c2c;
            --muted: #72795f;
            --white: #ffffff;
            --border: #e5e2d5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: var(--cream);
            color: var(--dark-olive);
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .eyebrow {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        h1 {
            margin: 10px 0;
            font-size: clamp(26px, 4vw, 36px);
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .button {
            display: inline-block;
            padding: 11px 15px;
            border-radius: 8px;
            background: var(--olive);
            color: white;
            text-decoration: none;
            border: 0;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .button:hover {
            background: var(--dark-olive);
        }

        .summary {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary strong {
            display: block;
            font-size: 28px;
            margin-top: 8px;
        }

        .table-wrap {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            text-align: left;
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            vertical-align: middle;
        }

        th {
            background: #eeeddf;
            color: var(--dark-olive);
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .user-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .muted {
            color: var(--muted);
            overflow-wrap: anywhere;
            line-height: 1.6;
        }

        .empty {
            padding: 35px 20px;
            text-align: center;
            color: var(--muted);
            line-height: 1.7;
        }

        .pagination {
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            body {
                padding: 18px 12px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <header class="topbar">
        <div>
            <div class="eyebrow">Artfy / User Management</div>
            <h1>All Art Lovers</h1>
            <div class="subtitle">
                View registered art lovers and their account information.
            </div>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="button">
            ← Back to Dashboard
        </a>
    </header>

    <section class="summary">
        <div class="subtitle">Registered art lovers on this page</div>
        <strong>{{ $artLovers->count() }}</strong>
        <div class="subtitle">
            Total registered art lovers: {{ $artLovers->total() }}
        </div>
    </section>

    <div class="table-wrap">
        @if ($artLovers->isNotEmpty())

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Art Lover</th>
                        <th>Email Address</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($artLovers as $artLover)
                        <tr>
                            <td>
                                {{ $artLovers->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="user-name">
                                    {{ $artLover->name }}
                                </div>
                                <div class="muted">
                                    User ID: {{ $artLover->id }}
                                </div>
                            </td>

                            <td>{{ $artLover->email }}</td>

                            <td>
                                {{ $artLover->created_at
                                    ? $artLover->created_at->format('d M Y')
                                    : 'Unavailable' }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.art-lovers.show', $artLover) }}"
                                    class="button"
                                >
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else

            <div class="empty">
                <h3>No Art Lovers Found</h3>
                There are currently no registered art-lover accounts.
            </div>

        @endif
    </div>

    <div class="pagination">
        {{ $artLovers->links() }}
    </div>

</div>
</body>
</html>
