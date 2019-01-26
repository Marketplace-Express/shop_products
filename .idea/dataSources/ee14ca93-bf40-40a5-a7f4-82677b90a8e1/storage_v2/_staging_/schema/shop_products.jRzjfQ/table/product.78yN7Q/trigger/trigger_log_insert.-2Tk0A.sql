drop trigger if exists trigger_log_insert;
create trigger trigger_log_insert
  after INSERT
  on product
  for each row
begin
  insert into admins_logs (action, user_id, product_id, action_data, done_at)
  VALUES
  ('INSERT',
   new.product_user_id,
   new.product_id,
   json_object(
       'product_title', new.product_title,
       'product_category_id', new.product_category_id,
       'product_type', new.product_type,
       'product_price', new.product_price,
       'product_sale_price', new.product_sale_price,
       'product_end_sale_time', new.product_sale_end_time,
       'product_weight', new.product_weight,
       'product_custom_page_id', new.product_custom_page_id,
       'product_link_slug', new.product_link_slug,
       'product_vendor_id', new.product_vendor_id,
       'product_brand_id', new.product_brand_id
     ),
   now());
end;

