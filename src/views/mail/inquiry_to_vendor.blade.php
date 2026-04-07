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
			
				<p>If you need additional assistance, or you did not make this change, please contact {{ getDefault('email')}}</p>
				<p style="padding:0px;margin:0px;">Thanks</p>
				<p style="padding:0px;margin:0px;">{{$vendor->name}}</p>
			</div> --}}
			<div class="contentArea" style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; border-radius: 8px;">
				<p style="font-size: 18px; margin-bottom: 10px;">Dear {{ $vendor->name }},</p>
				
				<p style="font-size: 16px;">New service inquiry has been received and is scheduled for follow-up. Below are the details of the inquiry:</p>
				
				<ul style="font-size: 16px; list-style-type: none; padding: 0;">
					<li><strong>Client Name:</strong> {{ $quoteEnq->client->fullName }}</li>
					{{-- <li><strong>Service Requested:</strong> {{ $quoteEnq->service_type }}</li> --}}
					<li><strong>Date of Inquiry:</strong> {{ now()->format('F j, Y') }}</li>
					<li><strong>Client Email:</strong> {{ $quoteEnq->client->email }}</li>
				</ul>
			
				{{-- <p style="font-size: 16px;">If you need additional assistance or have any questions about this inquiry, please do not hesitate to contact us at <a href="mailto:{{ getDefault('email') }}" style="color: #1a73e8;">{{ getDefault('email') }}</a>.</p> --}}
			
				{{-- <p style="font-size: 16px; margin-top: 20px;">Thank you for your continued partnership!</p> --}}
			
				{{-- <p style="font-size: 16px; margin-top: 10px;">Best regards,</p>
				<p style="font-size: 16px; font-weight: bold;">{{ $vendor->name }}</p>
				<p style="font-size: 16px;">{{ $companyName }} | <a href="mailto:{{ getDefault('email') }}" style="color: #1a73e8;">{{ getDefault('email') }}</a></p> --}}
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
