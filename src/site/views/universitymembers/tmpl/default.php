<?php

// no direct access
defined( '_JEXEC' ) or die;

JHtml::_( 'behavior.keepalive' );
JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.formvalidation' );
JHtml::_( 'formbehavior.chosen', 'select' );

$eventRegistrations = array();
foreach ( $this->event_registrations as $reg ) {
	$eventRegistrations[$reg->member_id][$reg->event_id] = strtotime( $reg->expires );;
}
?>

<!--</style>-->
<script type="text/javascript">
	getScript( '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', function () {
		jQuery( document ).ready( function () {
			jQuery( '#form-member' ).submit( function ( event ) {
			} );
		} );
	} );
</script>

<h1>University Members (current)</h1>

<p>View:
	<?php
	foreach( $this->layouts as $layout => $text ) {
		$href = JRoute::_( 'index.php?option=com_swa&view=universitymembers&layout=' . $layout );
		echo "<a href='$href' title='$text'>" . ucfirst( $layout ) . "</a>\n";
	}
	?>
</p>

<p>
	Here you can see all current registered members of your university that you have approved.
</p>

<table>
	<tr>
		<th>Id</th>
		<th>Name</th>
		<th>Paid</th>
		<th>Committee</th>
		<th>Discipline</th>
		<th>Level</th>
		<th>Action</th>
		<th>Event Registration</th>
	</tr>

	<?php
	foreach ( $this->items as $item ) {
		if ( !$item->confirmed_university || $item->graduated ) {
			continue;
		}
		echo "<tr>\n";
		echo "<td>" . $item->id . "</td>\n";
		echo "<td>" . $item->name . "</td>\n";
		if ( $item->paid ) {
			echo "<td bgcolor='#CCFF33'>Yes</td>\n";
		} else {
			echo "<td bgcolor='#FF6666'>No</td>\n";
		}
		echo "<td>" . ( $item->club_committee ? 'Yes' : 'No' ) . "</td>\n";
		echo "<td>" . $item->discipline . "</td>\n";
		echo "<td>" . $item->level . "</td>\n";

		echo '<td>';
		echo '<form id="form-universitymembers-graduate-' .
			$item->id .
			'" method="POST" action="' .
			JRoute::_( 'index.php?option=com_swa&task=universitymembers.graduate' ) .
			'">' .
			'<input type="hidden" name ="member_id" value="' .
			$item->id .
			'" />' .
			'<a href="javascript:{}" onclick="document.getElementById(\'form-universitymembers-graduate-' .
			$item->id .
			'\').submit(); return false;">(graduate)</a>' .
			JHtml::_( 'form.token' ) .
			'</form>';
		echo '<form id="form-universitymembers-unapprove-' .
			$item->id .
			'" method="POST" action="' .
			JRoute::_( 'index.php?option=com_swa&task=universitymembers.unapprove' ) .
			'">' .
			'<input type="hidden" name ="member_id" value="' .
			$item->id .
			'" />' .
			'<a href="javascript:{}" onclick="document.getElementById(\'form-universitymembers-unapprove-' .
			$item->id .
			'\').submit(); return false;">(unapprove)</a>' .
			JHtml::_( 'form.token' ) .
			'</form>';
		echo '</td>';

		echo "<td><ul>";
		foreach ( $this->events as $event ) {
			echo "<li>" . $event->name . ' ';
			if (
				array_key_exists( $item->id, $eventRegistrations ) &&
				array_key_exists( $event->id, $eventRegistrations[$item->id] ) &&
				$eventRegistrations[$item->id][$event->id] >= strtotime( '-3 day', time() )
			) {
				//registered for the event
				echo '<form id="form-universitymembers-unregister' . $item->id . '" method="POST" action="' .
					JRoute::_( 'index.php?option=com_swa&task=universitymembers.unregister' ) .
					'">' .
					'<input type="hidden" name ="member_id" value="' .
					$item->id .
					'" />' .
					'<input type="hidden" name ="event_id" value="' .
					$event->id .
					'" />' .
					'<a href="javascript:{}" onclick="document.getElementById(\'form-universitymembers-unregister-' . $item->id . '\').submit(); return false;">(unregister)</a>' .
					JHtml::_( 'form.token' ) .
					'</form>';
			} else {
				//not registered for the event
				echo '<form id="form-universitymembers-register-' . $item->id . '" method="POST" action="' .
					JRoute::_( 'index.php?option=com_swa&task=universitymembers.register' ) .
					'">' .
					'<input type="hidden" name ="member_id" value="' .
					$item->id .
					'" />' .
					'<input type="hidden" name ="event_id" value="' .
					$event->id .
					'" />' .
					'<a href="javascript:{}" onclick="document.getElementById(\'form-universitymembers-register-' . $item->id . '\').submit(); return false;">(register)</a>' .
					JHtml::_( 'form.token' ) .
					'</form>';
			}
			echo "</li>\n";
		}
		echo "</ul></td>\n";
		echo "</tr>\n";
	}
	?>

</table>
