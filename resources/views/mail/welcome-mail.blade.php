<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to I-Sit Corp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            background-color: #007bff;
            color: #fff;
            padding: 10px 0;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .content h2 {
            color: #007bff;
        }

        .otp {
            font-weight: bold;
            font-size: 24px;
            text-align: center;
            color: #007bff;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Welcome to I-Sit Corp</h1>
        </div>
        <div class="content">
            <p>Dear {{ $user['name'] }},</p>
            <p>Welcome to I-Sit Corp! We're thrilled to have you join our growing community of users, professionals, and
                corporate partners.</p>

            <h2>About I-Sit Corp</h2>
            <p>At I-Sit Corp, we are dedicated to revolutionizing the way businesses and individuals operate through
                cutting-edge technology. Our platform offers innovative solutions that simplify tasks, streamline
                operations, and create a seamless user experience.</p>

            <h2>What is the I-Sit App?</h2>
            <ul>
                <li><strong>Simplify your daily operations.</strong></li>
                <li><strong>Connect professionals with clients efficiently.</strong></li>
                <li><strong>Provide corporate users with tools for optimizing performance.</strong></li>
            </ul>
            <p>Whether you're an individual, a professional, or a corporate team, the I-Sit App adapts to your needs,
                ensuring ease of use and maximum impact.</p>

        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} I-Sit Corp. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
