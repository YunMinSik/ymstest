<?php

namespace App\Controllers;

use PDO;
use PDOException;

class UserController extends Controller
{
	public function join ($request, $response, $args) 
	{
		if (!$request->getParam('user_id')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'miss ID.'
			);

			return $response->withJson($result);
		}

		if (!$request->getParam('user_name')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'miss NAME.'
			);

			return $response->withJson($result);
		}

		if (!$request->getParam('password')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'miss PASSWORD.'
			);

			return $response->withJson($result);
		}

		if (!$request->getParam('reg_datetime')) {
			$regDate = date('YmdHis');
		} else {
			$regDate = $request->getParam('reg_datetime');
		}

		$user = $this->getUserIdCheck($request->getParam('user_id'));

		if (!$user) {

			$statement = $this->c->db->prepare("INSERT INTO user (user_id, user_name, password, reg_datetime) VALUES (:user_id, :user_name, :password, :reg_datetime)");
			
			try {
				$statement->execute([
					'user_id' =>$request->getParam('user_id'),
					'user_name' =>$request->getParam('user_name'),
					'password' =>$request->getParam('password'),
					'reg_datetime' =>$regDate
				]);
			} catch (PDOException $e) {
				return $response->withStatus(400);

			}
			
			$result = array(
				'status'=>'success',
				'user_id' =>$request->getParam('user_id'),
				'user_name' =>$request->getParam('user_name'),
				'password' =>$request->getParam('password'),
				'reg_datetime' =>$regDate
			);

			return $response->withJson($result);

		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'Duplicate ID.'
			);

			return $response->withJson($result);
		}
	}

	public function modify ($request, $response, $args) 
	{
		$user = $this->getUserIdCheck($_SESSION['user_id']);

		if (!$request->getParam('user_name') && !$request->getParam('password')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'No edits.'
			);

			return $response->withJson($result);
		}

		if ($user) {
			$sql = "";
			if ($request->getParam('user_name')) {
				$sql .= "user_name = :user_name,";
			}

			if ($request->getParam('password')) {
				$sql .= "password = :password,";
			}
			$sql = substr($sql,0,-1);

			$statement = $this->c->db->prepare("UPDATE user SET ".$sql." WHERE user_id=:user_id");

			try {
				$statement->bindParam("user_id", $_SESSION['user_id']);
				if ($request->getParam('user_name')) {
					$statement->bindParam("user_name", $request->getParam('user_name'));
				}

				if ($request->getParam('password')) {
					$statement->bindParam("password", $request->getParam('password'));
				}
				$statement->execute();

			} catch (PDOException $e) {
				return $response->withStatus(400);

			}

			$user = $this->getUserIdCheck($_SESSION['user_id']);

			$_SESSION['user_id'] = $_SESSION['user_id'];
			$_SESSION['user_name'] = $user['user_name'];

			$result = array(
				'status'=>'success',
				'user_id' =>$_SESSION['user_id'],
				'user_name' =>$user['user_name'],
				'password' =>$user['password']
			);

			return $response->withJson($result);

		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'Not logged in.'
			);

			return $response->withJson($result);
		}
	}

	protected function getUserIdCheck ($id) 
	{
		$statement = $this->c->db->prepare("SELECT * FROM user WHERE user_id = :id");
		$statement->execute(['id' => $id]);
		$result = $statement->fetch(PDO::FETCH_ASSOC);

		if ($statement->rowCount() === 0 ) {
			return false;
		}else {
			return $result;
		}
	}

	public function login ($request, $response, $args)
	{
		if (!$request->getParam('user_id')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'miss ID.'
			);

			return $response->withJson($result);
		}

		if (!$request->getParam('password')) {
			$result = array(
				'status'=>'fail', 
				'message'=>'miss PASSWORD.'
			);

			return $response->withJson($result);
		}

		$user = $this->getUserIdCheck($request->getParam('user_id'));

		if ($user) {
			if ($user['password'] == $request->getParam('password')) {			
				$user_id = $user['user_id'];
				$user_name = $user['user_name'];

				$result = array(
					'status'=>'success',
					'user_id' =>$user_id,
					'user_name' =>$user_name
				);

				$_SESSION['user_id'] = $user_id;
				$_SESSION['user_name'] = $user_name;

				return $response->withJson($result);
			} else {
				$result = array(
					'status'=>'fail', 
					'message'=>'Password is incorrect.'
				);

				return $response->withJson($result);
			}

		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'ID not found.'
			);

			return $response->withJson($result);
		}
	}

	public function logout ($request, $response, $args)
	{
		unset($_SESSION['user_id']);
		unset($_SESSION['user_name']);
	}

}