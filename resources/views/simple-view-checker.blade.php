<!DOCTYPE html>
<html>
<head>
    <title>View Checker</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .exists { color: green; }
        .missing { color: red; }
        ul { list-style: none; padding: 0; }
        li { padding: 5px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>View Checker Results</h1>
    <ul>
        @foreach($results as $view => $status)
            <li class="{{ str_contains($status, 'EXISTS') ? 'exists' : 'missing' }}">
                {{ $view }}: {{ $status }}
            </li>
        @endforeach
    </ul>
    <a href="/">Back to Home</a>
</body>
</html>