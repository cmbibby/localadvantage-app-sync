<?php

namespace LA_App_Sync;

class Delete_Media
{
	public function __construct()
	{

		add_action('before_delete_post', array($this, 'delete_post_attachments'),10,2);
	}


	public function delete_post_attachments($post_id, $post)
	{

		if(! $post->post_type == 'sw_offers' || ! $post->post_type == 'gs_offers'){
			return;
		}

		$logo_id = get_post_thumbnail_id($post);
		wp_delete_attachment($logo_id, true);
		$gallery_attachments = get_field('offer_gallery', $post_id);
		foreach ($gallery_attachments as $attachment) {
		  wp_delete_attachment( $attachment['id'], true );
		}
	}
}
