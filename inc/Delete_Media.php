<?php

namespace LA_App_Sync;

class Delete_Media
{
	public function __construct()
	{

		add_action('before_delete_post', array($this, 'delete_post_attachments'));
	}


	public function delete_post_attachments($post_id)
	{
		$attachments = get_attached_media( '', $post_id );
		foreach ($attachments as $attachment) {
		  wp_delete_attachment( $attachment->ID, 'true' );
		}
	}
}
