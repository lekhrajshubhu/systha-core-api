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
					<img src="{{$logo}}"  alt="logo">
				</div>
				{{-- <p class="company_name">{{default_company_name()}}</p> --}}
			</div>
			{{-- <div class="contentArea">
				<p>Dear {{$vendor->name}},</p>
				<p>new schedule service </p>
			
				<p>If you need additional assistance, or you did not make this change, please contact {{$vendor->email}}</p>
				<p style="padding:0px;margin:0px;">Thanks</p>
				<p style="padding:0px;margin:0px;">{{$vendor->name}}</p>
			</div> --}}
            <div class="contentArea" style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; border-radius: 8px;">
                <p style="font-size: 18px; margin-bottom: 10px;">Dear {{ $client->fname }},</p>
                
                <p style="font-size: 16px;">Thank you for reaching out to us! We have successfully received your inquiry, and we are currently reviewing the details. One of our team members will contact you shortly to confirm the next steps and provide further assistance.</p>
            
                {{-- <p style="font-size: 16px;">Here's a summary of your inquiry:</p>
                <ul style="font-size: 16px; list-style-type: none; padding: 0;">
                    <li><strong>Service Requested:</strong> {{ $serviceType }}</li>
                    <li><strong>Date of Inquiry:</strong> {{ now()->format('F j, Y') }}</li>
                </ul> --}}
            
                <p style="font-size: 16px;">If you have any questions in the meantime, feel free to reach out to us directly at <a href="mailto:{{ $vendor->contact->email }}" style="color: #1a73e8;">{{ $vendor->contact->email }}</a>. We’re here to assist you!</p>
            
                <p style="font-size: 16px; margin-top: 20px;">Thank you again for your inquiry. We look forward to assisting you with your service request!</p>
            
                <p style="font-size: 16px; margin-top: 10px;">Best regards,</p>
                <p style="font-size: 16px; font-weight: bold;">{{ $vendor->name }}</p>
                <p style="font-size: 16px;">{{ $vendor->contact->mobile_no }} | <a href="mailto:{{ $vendor->contact->email }}" style="color: #1a73e8;">{{ $vendor->contact->email }}</a></p>
            </div>
            
		</div>
		<div class="footer">
			<div class="lowerFoot">
				<div class="copyRight">
                    <p> &copy;Copyright {{Date('Y')." ".$vendor->name}}. All Rights Reserved.</p>
				</div>
				<div class="email">
					<p style="color:#fff">{{$vendor->email}}</p>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
