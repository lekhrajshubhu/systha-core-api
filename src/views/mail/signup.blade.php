<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .wrapper{
            max-width: 450px;
            margin: 0 auto;
            font-family: system-ui;
            font-size: 14px;
        }
        .wrapper .upperArea{
            border-bottom: 1px solid #dadada;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .wrapper .upperArea .logo img{
            height: 50px;
        }
        .wrapper .footer{
            background: #42555c;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }

        .footer p{
            font-size: 14px;
            text-transform: capitalize;
        }
        .footer .email p{
            font-size: 14px;
            text-transform: lowercase;
        }
        @media only screen and(max-width:450px){
            .wrapper{
                width: 350px;
            }
        }
        .company_name{
            padding: 0;
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            margin-left: 10px;
            color:#42555c;
        }
        .upper-part{
            background-color: #fafafa;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="upper-part">
            <div class="upperArea">
                <div class="logo">
                    <img src="{{ $logo ?? 'noimage.png' }}"  alt="logo">
                </div>
            </div>
            <div class="contentArea">
                <p>Dear {{ trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: 'User' }},</p>
                <p>Welcome to Systha! Your account has been created successfully.</p>
                <p>Your login email: <strong>{{ $user->email }}</strong></p>
                <p style="padding:0;margin:0;">If you did not request this account, please contact our support team.</p>
                <p style="padding:0;margin:0;">Thanks</p>
            </div>
        </div>
        <div class="footer">
            <div class="lowerFoot">
                <div class="copyRight">
                    <p>&copy;Copyright {{ Date('Y') }}. All Rights Reserved.</p>
                </div>
                <div class="email">
                    <p style="color:#fff"></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
