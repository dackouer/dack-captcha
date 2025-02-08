<?php
	namespace Dack;

	use support\Request;

	class Captcha{
		private $key = 'numcode';
		private $is_redis = true;

		/**
		 * [createCaptcha description]
		 * @param  Request $request [description]
		 * @return [type]           [description]
		 */
		public function create(Request $request){
			$builder = new CaptchaBuilder();
			// 生成验证码
			$builder->build();
			// 获取code
			$code = $builder->getPhrase();
			$this->setSession($request,$code);
			// 获取图片二进制数据
			$img = $builder->get();

			return $img;
			// var_dump($img);
			// $base64Img = "data:image/png;base64,".base64_encode($img);
			// return ['code' => 0,'msg' => 'success','data' => $base64Img];
			// return response($img,200,['Content-Type' => 'image/jpeg']);
		}

		/**
		 * [check description]
		 * @param  Request $request [description]
		 * @return [type]           [description]
		 */
		public function check(Request $request){
			$token = trim($request->header('token',''));
			$numcode = trim($request->post($this->key,''));
			$session_code = $request->session()->get($this->key.'_'.$token);
			if(!$session_code && $this->is_redis){
				if(!$token){
					return false;
				}
				$session_code = \support\Redis::get($this->key.'_'.$token);
			}

			if(!$numcode || !$session_code || strtolower($numcode) !== strtolower($session_code)){
				return false;
			}
			return true;
		}

		/**
		 * [setSession description]
		 * @param Request $request [description]
		 * @param [type]  $code    [description]
		 */
		private function setSession(Request $request,$code){
			$token = trim($request->header('token',$request->input('token','')));
			// set session
			$request->session()->set($this->key.'_'.$token,$code);
			// set redis
			if($this->is_redis && $token){
				\support\Redis::set($this->key.'_'.$token,$code);
			}
		}

	}
?>