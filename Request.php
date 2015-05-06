<?php
session_start();
include_once('./config/database.php');
include_once('./config/Pdb.php');
$_POST = $_REQUEST;
$db = Pdb::getDb();
if ( isset( $_POST['model'] ) ) {
	switch ( $_POST['model'] ) {
		case 'finish':
			$tag = false;
			$name = isset( $_POST['name'] ) ? $_POST['name'] : $tag = true;
			$mobile = isset( $_POST['mobile'] ) ? $_POST['mobile'] : $tag = true;
			$city = isset( $_POST['city'] ) ? $_POST['city'] : $tag = true;
			$address = isset( $_POST['address'] ) ? $_POST['address'] : $tag = true;
			if( $tag ){
				print json_encode( array( "code" => 2 , "msg" => "请填写必填项" ) );
				exit;
			}
			$sql = "select id from user_info where mobile=" . $db->quote( $mobile );
			$rs = $db->getOne( $sql );
			if( $rs ){
				print json_encode( array( "code" => 3 , "msg" => "该手机号码已经提交过了" ) );
				exit;
			}
			$sql = "insert into user_info(name,mobile,city,address) values (" . $db->quote( $name ) 
				. "," . $db->quote( $mobile ) . "," . $db->quote( $city ) . "," . $db->quote( $address ) . ")";
			$db->execute( $sql );
			print json_encode( array( "code" => 1 , "msg" => "提交成功" ));
			exit;
			break;

		case 'weixinjs':
			$url = isset( $_POST['url'] ) ? $_POST['url'] : "http://lancasterld.samesamechina.com";
			$time = file_get_contents( "time.txt" );
			$access_token = file_get_contents( "access_token.txt" );
			$ticket = file_get_contents( "ticket.txt" );
			if( time() - $time >= 100 ){
				//token过期重新获取
				$appid = "wx737a6d5fe4d19c89";
				$secret = "f6f990e973d09b7d3da722b9390bfd09";
				$token = file_get_contents( "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret );
				$token = json_decode( $token , true );
				$access_token = $token['access_token'];
				//获取ticket
				$ticketfile = file_get_contents( "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi" );
				$ticketfile = json_decode( $ticketfile , true );
				$ticket = $ticketfile['ticket'];
				$time = time();
				$fp = fopen( "time.txt" , "w");
				fwrite( $fp , $time );
				fclose( $fp );
				$fp = fopen( "access_token.txt" , "w");
				fwrite( $fp , $access_token );
				fclose( $fp );
				$fp = fopen( "ticket.txt" , "w");
				fwrite( $fp , $ticket );
				fclose( $fp );
			}
			$time = time();
			$ticketstr = "jsapi_ticket=" . $ticket . "&noncestr=asdkhaedhqwui&timestamp=" . $time . "&url=" . $url;
			$sign = sha1( $ticketstr );
			echo json_encode( array( "time" => $time . "" , "access_token" => $access_token , "sign" => $sign ) );
			exit;
			break;
		default:
			# code...
			print json_encode( array( "code" => 9999 , "msg" => "No Model" ) );
			exit;
			break;
	}
}		
print json_encode( array( "code" => 9999 , "msg" => "No Model" ) );
exit;
exit;
