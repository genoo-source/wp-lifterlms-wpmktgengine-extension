<?php
/**
 * Class LifterLMS
 */
class LifterLMSExtension
{
	/**
	 * Checks checks if the LLMS WooCommerce integration is enabled
	 * @return boolean
	 */
	public static function isWooCommerceEnalbed()
	{
		if(get_option('lifterlms_woocommerce_enabled') == 'yes') {
			return true;
		}
		return false;
	}

	/**
	 * Checks if the WooCommerce plugin is installed & activated
	 * @return boolean
	 */
	public static function isWooCommerceInstalled()
	{

		if(class_exists('WooCommerce')) {
			return true;
		}
		return false;
	}

	/**
	 * Converts order object to Product ID
	 *
	 * @param bool $obj
	 * @return array|bool
	 */
	public static function convertObjectToWPMEProduct($obj = FALSE)
	{
		if(is_object($obj)){
			$productTitlte = isset($obj->product_title) ? $obj->product_title : '';
			$productId = isset($obj->product_id) ? $obj->product_id : 0;
			$productPrice = isset($obj->product_price) ? $obj->product_price : 0;
			$productSku = isset($obj->product_sku) ? $obj->product_sku : '';
			$productType = isset($obj->product_type) ? $obj->product_type : '';
			$data = array(
				'categories' => array(),
				'id' => $productId,
				'name' => $productTitlte,
				'price' => $productPrice,
				'sku' => $productSku,
				'tags' => '',
				'type' => $productType,
				'url' => '',
				'vendor' => '',
				'weight' => 0,
				'option1_name' => '',
				'option1_value' => '',
				'option2_name' => '',
				'option2_value' => '',
				'option3_name' => '',
				'option3_value' => '',
			);
			return $data;
		}
		return FALSE;
	}

	/**
	 * @param $product_id_internal
	 * @param $total
	 * @param $unit
	 * @param $external_id
	 * @param $title
	 * @return array
	 */
	public static function createCartContents($product_id_internal, $total, $unit, $external_id, $title)
	{
		$r = array();
		$array['product_id'] = (int)$product_id_internal;
		$array['quantity'] = 1;
		$array['total_price'] = (float)$total;
		$array['unit_price'] = (float)$unit;
		$array['external_product_id'] = (int)$external_id;
		$array['name'] = $title;
		$r[] = $array;
		return $r;
	}
}