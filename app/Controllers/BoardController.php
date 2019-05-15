<?php

namespace App\Controllers;

use PDO;
use PDOException;

class BoardController extends Controller
{
	public function page ($request, $response, $args) 
	{

		$page = $args['no'];

		$list = 5;

		$limit = ($page - 1) * 5;

		$statement = $this->c->db->prepare("SELECT * FROM board WHERE no != '' limit {$limit}, {$list}");
		$statement ->execute();
		$row = $statement->fetchAll(PDO::FETCH_ASSOC);

		if ($row) {
			return $response->withJson($row);
		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'No data.'
			);

			return $response->withJson($result);
		}
	}

	public function view ($request, $response, $args) 
	{

		$statement = $this->c->db->prepare("SELECT * FROM board WHERE no = :no");
		$statement ->execute(['no' => $args['no']]);
		$row = $statement->fetch(PDO::FETCH_ASSOC);

		if ($row) {
			$result = array(
				'no' =>$row['no'],
				'user_name' =>$row['user_name'],
				'subject' =>$row['subject'],
				'content' =>$row['content'],
				'create_datetime' =>$row['create_datetime']
			);

			return $response->withJson($result);
		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'No data.'
			);

			return $response->withJson($result);
		}
	}

	public function write ($request, $response, $args) 
	{
		if ($_SESSION['user_name']) {
			if (!$request->getParam('subject')) {
				$result = array(
					'status'=>'fail', 
					'message'=>'miss SUBJECT.'
				);

				return $response->withJson($result);
			}

			if (!$request->getParam('content')) {
				$result = array(
					'status'=>'fail', 
					'message'=>'miss Content.'
				);

				return $response->withJson($result);
			}

			if (!$request->getParam('create_datetime')) {
				$regDate = date('YmdHis');
			} else {
				$regDate = $request->getParam('create_datetime');
			}

			$stmt = $this->c->db->prepare("SELECT ifnull(max(no),0) as cnt FROM board");
			$stmt->execute();
			$cnt = $stmt->fetch(PDO::FETCH_ASSOC);
			$no = $cnt['cnt'] + 1;

			$statement = $this->c->db->prepare("INSERT INTO board (no, user_name, subject, content, create_datetime) VALUES (:no, :user_name, :subject, :content, :create_datetime)");
			
			try {
				$statement->execute([
					'no' =>$no,
					'user_name' =>$_SESSION['user_name'],
					'subject' =>$request->getParam('subject'),
					'content' =>$request->getParam('content'),
					'create_datetime' =>$regDate
				]);
			} catch (PDOException $e) {
				return $response->withStatus(400);

			}

			$result = array(
				'status'=>'success',
				'no' =>$no,
				'user_name' =>$_SESSION['user_name'],
				'subject' =>$request->getParam('subject'),
				'content' =>$request->getParam('content'),
				'create_datetime' =>$regDate
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

	public function modify ($request, $response, $args) 
	{

		$statement = $this->c->db->prepare("SELECT * FROM board WHERE no = :no");
		$statement ->execute(['no' => $args['no']]);
		$row = $statement->fetch(PDO::FETCH_ASSOC);

		if ($_SESSION['user_name'] == $row['user_name']) {

			if (!$request->getParam('subject') && !$request->getParam('content')) {
				$result = array(
					'status'=>'fail', 
					'message'=>'No edits.'
				);

				return $response->withJson($result);
			}

			$sql = "";
			if ($request->getParam('subject')) {
				$sql .= "subject = :subject,";
			}

			if ($request->getParam('content')) {
				$sql .= "content = :content,";
			}
			$sql = substr($sql,0,-1);

			$statement = '';

			$statement = $this->c->db->prepare("UPDATE board SET ".$sql." WHERE no=:no");

			try {
				$statement->bindParam("no", $args['no']);
				if ($request->getParam('subject')) {
					$statement->bindParam("subject", $request->getParam('subject'));
				}

				if ($request->getParam('content')) {
					$statement->bindParam("content", $request->getParam('content'));
				}
				$statement->execute();

			} catch (PDOException $e) {
				return $response->withStatus(400);

			}

			$result = array(
				'status'=>'success',
				'no' =>$args['no']
			);

			return $response->withJson($result);

		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'Can not modify.'
			);

			return $response->withJson($result);
		}
	}

	public function delete ($request, $response, $args) 
	{
	
		$statement = $this->c->db->prepare("SELECT * FROM board WHERE no = :no");
		$statement ->execute(['no' => $args['no']]);
		$row = $statement->fetch(PDO::FETCH_ASSOC);

		if ($_SESSION['user_name'] == $row['user_name']) {

			$statement = $this->c->db->prepare("DELETE FROM board WHERE no = :no");
			$statement->execute(['no'=>$args['no']]);

				$result = array(
					'status'=>'success', 
					'message'=>'Delete complete.'
				);

				return $response->withJson($result);

		} else {
			$result = array(
				'status'=>'fail', 
				'message'=>'Can not delete.'
			);

			return $response->withJson($result);

		}
	}

}