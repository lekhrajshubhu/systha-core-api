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
					<img src="{{$vendor->logo ? $vendor->logo : 'noimage.png'}}"  alt="logo">
				</div>
				{{-- <p class="company_name">{{default_company_name()}}</p> --}}
			</div>
			<div class="contentArea">
				<p>Dear {{$vendor->name}},</p>
				<p>new schedule service </p>
				{{-- <p>Your new password is: </p>
				<h4>{{ $password }}</h4> --}}
				<p>If you need additional assistance, or you did not make this change, please contact {{ getDefault('email')}}</p>
				<p style="padding:0px;margin:0px;">Thanks</p>
				<p style="padding:0px;margin:0px;">{{$vendor->name}}</p>
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
