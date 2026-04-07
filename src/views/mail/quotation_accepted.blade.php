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
            <div class="contentArea" style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; border-radius: 8px;">
                <p style="font-size: 18px; margin-bottom: 10px;">Dear {{ $vendor->name }},</p>
            
                <p style="font-size: 16px;">This is a system notification to inform you that the quotation for the requested service has been successfully accepted by the client.</p>
            
                <p style="font-size: 16px;">Please proceed with converting the accepted quotation into an official appointment. If any additional information or clarification is required, feel free to contact the client directly.</p>
            
                <p style="font-size: 16px;">Thank you for your prompt attention to this matter. We look forward to seeing the appointment scheduled soon.</p>
            
                {{-- <p style="font-size: 16px; margin-top: 20px;">Best regards,</p>
                <p style="font-size: 16px; font-weight: bold; margin: 0;">The {{ config('app.name') }} Team</p> --}}
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
