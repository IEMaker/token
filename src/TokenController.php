<?php
namespace IEMaker\Token;

use Illuminate\Routing\Controller;

class TokenController extends Controller {

	public function getToken(Token $token) {
		return $token->init();
	}

}
