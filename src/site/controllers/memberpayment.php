<?php

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

class SwaControllerMemberPayment extends SwaController
{

	public function submit()
	{
		// Get the POST data
		$token = $this->input->getString('stripeToken');

		$user = JFactory::getUser();
		/** @var SwaModelMemberPayment $model */
		$model  = $this->getModel('MemberPayment');
		$member = $model->getMember();

		// Check successfully got the user and all the info we need to process the transaction
		if (!$user || !isset($user->id) || !isset($user->name) || !isset($user->email))
		{
			$message = "Unable to retrieve user. " . var_export($user, true);
			JLog::add($message, JLog::ERROR, 'com_swa.payment_process');
			die("Unable to identify user. Please contact webmaster@swa.co.uk if this problem continues.");
		}

		try
		{
			$charge = \Stripe\Charge::create(
				array(
					'description'          => "SWA Membership for {$user->name}",
					'amount'               => 500,
					'currency'             => 'GBP',
					'receipt_email'        => $user->email,
					'statement_descriptor' => "SWA Membership",
					'source'               => $token,
					'metadata'             => array(
						'user_id'   => $user->id,
						'user_name' => $user->name
					)
				)
			);
		}
		catch (\Stripe\Error\Card $e)
		{
			// Card declined
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			die("You're card was declined. Please contact webmaster@swa.co.uk if this continues to happen.");
		}
		catch (\Stripe\Error\RateLimit $e)
		{
			// Too many requests made to the API too quickly
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "The website is in high demand and we were unable to process your payment at this time";
			$error_msg .= " - try again later. \r\nPlease contact webmaster@swa.co.uk if this continues to happen.";
			die($error_msg);
		}
		catch (\Stripe\Error\InvalidRequest $e)
		{
			// Invalid parameters were supplied to Stripe's API
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! We sent the wrong data to our payment provider.\r\n";
			$error_msg .= "Please contact webmaster@swa.co.uk to tell them they screwed up.";
			die($error_msg);
		}
		catch (\Stripe\Error\Authentication $e)
		{
			// Authentication with Stripe's API failed (maybe you changed API keys recently)
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! We were unable to authenticate with our payment provider. ";
			$error_msg .= "Please contact webmaster@swa.co.uk and tell them they screwed up.\r\n";
			die($error_msg);
		}
		catch (\Stripe\Error\ApiConnection $e)
		{
			// Network communication with Stripe failed
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! There was a network communication error - please try again.\r\n";
			$error_msg .= "Contact webmaster@swa.co.uk if this continues to happen.\r\n";
			die($error_msg);
		}
		catch (\Stripe\Error\Base $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! There was an unknown error processing your transaction - please try again.\r\n";
			$error_msg .= "Contact webmaster@swa.co.uk if this continues to happen.\r\n";
			die($error_msg);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! There was an unknown error processing your transaction - please try again.\r\n";
			$error_msg .= "Contact webmaster@swa.co.uk if this continues to happen.\r\n";
			die($error_msg);
		}

		// Do some sense checking to make sure the payment didn't fail - probably not needed
		if ($charge->failure_code != null && $charge->failure_message != null
			&& $charge->paid != true && $charge->captured != true)
		{
			JLog::add("Stripe charge didn't return successful.", JLog::ERROR, 'com_swa.payment_process');
			$error_msg = "Oops! There was an unknown error processing your transaction - please try again.\r\n";
			$error_msg .= "Contact webmaster@swa.co.uk if this continues to happen.\r\n";
			die($error_msg);
		}

		// Set member paid
		$this->setMemberPaid($member->id);

		$this->setRedirect(JRoute::_('index.php'));
	}

	private function setMemberPaid($member_id)
	{
		// Update the membership status for the member!
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$now        = time();
		$seasonEnd  = strtotime("1st June");
		$seasonName = $now < $seasonEnd ? date("Y", strtotime('-1 year', $now)) : date("Y", $now);

		$subQuery = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__swa_season', 'season'))
			->where($db->qn('season.year') . ' LIKE "' . $seasonName . '%"');

		$columns = array('member_id', 'season_id');
		$values  = array($db->q($member_id), '(' . $subQuery . ')');

		$query
			->insert($db->quoteName('#__swa_membership'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if ($result === false)
		{
			JLog::add(
				"MemberPayment authorized but failed to update db. member_id: {$member_id}",
				JLog::ERROR, 'com_swa.payment_process'
			);
			die('Failed to record payment. Please contact webmaster@swa.co.uk ASAP to resolve this.');
		}

		$this->logAuditFrontend("Member({$member_id}) bought their membership for Season({$seasonName})");
	}

}
