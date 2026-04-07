<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        .wrapper {
            max-width: 450px;
            margin: 0 auto;
            font-family: system-ui;
            font-size: 14px;
        }

        .wrapper .upperArea {
            border-bottom: 1px solid #dadada;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .wrapper .upperArea .logo img {
            height: 50px;
        }

        .wrapper .footer {
            background: #42555c;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }

        .footer p {
            font-size: 14px;
            text-transform: capitalize;
        }

        .footer .email p {
            font-size: 14px;
            text-transform: lowercase;
        }

        @media only screen and(max-width:450px) {
            .wrapper {
                width: 350px;
            }
        }

        .company_name {
            padding: 0;
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            margin-left: 10px;
            color: #42555c;
        }

        .upper-part {
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
                    <img src="{{ $logo }}" alt="logo">
                </div>
            </div>
            <div class="contentArea"
                style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;  border-radius: 8px;">
                <p style="font-size: 18px; margin-bottom: 10px;">Dear {{ $vendor->name }},</p>

                <p style="font-size: 16px;">Thank you for providing the quotation for the requested service. After
                    careful consideration, we regret to inform you that we have decided not to move forward with your
                    proposal at this time.</p>

                <p style="font-size: 16px;">We appreciate the time and effort you put into preparing the quotation and
                    we value your services. However, we have chosen another option that aligns better with our current
                    needs and budget.</p>

                <p style="font-size: 16px;">We hope to have the opportunity to work with you in the future on other
                    projects, and we will certainly keep your contact information for any potential future services.
                    Should our needs change, we will reach out to you again.</p>

                <p style="font-size: 16px;">Thank you again for your time, and we wish you continued success with your
                    business.</p>

                <p style="font-size: 16px; margin-top: 20px;margin:0">Best regards,</p>
                <p style="font-size: 16px; font-weight: bold;margin:0">{{ $client->fname }} {{ $client->lname }}</p>
                <p style="font-size: 16px;margin:0">{{ $client->email }} | {{ $client->mobile_no }}</p>
            </div>


        </div>
        <div class="footer">
            <div class="lowerFoot">
                <div class="copyRight">
                    <p> &copy;Copyright {{ Date('Y') . ' ' . $vendor->name }}. All Rights Reserved.</p>
                </div>
                <div class="email">
                    <p style="color:#fff">{{ $vendor->email }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
