// افزودن شورت کد برای نمایش درصد تخفیف
add_shortcode('discount_percentage', 'custom_discount_percentage_shortcode');

function custom_discount_percentage_shortcode($atts) {
    // دریافت مشخصات (attributes) از شورت‌کد (ID محصول در صورت نیاز)
    $atts = shortcode_atts(array(
        'id' => null,
    ), $atts);

    // شناسایی محصول
    $product_id = $atts['id'] ? $atts['id'] : get_the_ID();
    $product = wc_get_product($product_id);

    // بررسی معتبر بودن محصول
    if (!$product || !$product->is_on_sale()) {
        return ''; // اگر محصول وجود ندارد یا تخفیف ندارد
    }

    // دریافت قیمت اصلی و قیمت فروش
    $regular_price = (float) $product->get_regular_price();
    $sale_price = (float) $product->get_sale_price();

    // بررسی صحت قیمت‌ها
    if ($regular_price <= 0 || $sale_price <= 0) {
        return ''; // اگر قیمت‌ها نامعتبر هستند
    }

    // محاسبه درصد تخفیف
    $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);

    // بازگرداندن درصد تخفیف
    return $discount_percentage . '% تخفیف';
}
