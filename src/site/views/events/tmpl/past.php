<?php

defined( '_JEXEC' ) or die;

JHtml::_( 'behavior.keepalive' );
JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.formvalidation' );
JHtml::_( 'formbehavior.chosen', 'select' );

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load( 'com_swa', JPATH_ADMINISTRATOR );
$doc = JFactory::getDocument();
$doc->addScript( JUri::base() . '/components/com_swa/assets/js/form.js' );
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

	<h1>Past Events</h1>

<?php
if ( empty( $this->items ) ) {
	echo "<p>There are no past events to display</p>";
} else {
	?>

	<table>
		<tr>
			<th>Name</th>
			<th>Date</th>
		</tr>

		<?php
		foreach ( $this->items as $item ) {
			// Skip if date is in the past!
			if( new DateTime( $item->date ) > new DateTime() ) {
				continue;
			}
			echo "<tr>\n";
			echo "<td><a href=''>" . $item->name . "</a></td>\n";
			echo "<td>" . $item->date . "</td>\n";
			echo "</tr>\n";
		}
		?>
	</table>

	<p>Yes, we know there have been more events than this... but these events predate this lovely website!</p>

	<?php
}
?>