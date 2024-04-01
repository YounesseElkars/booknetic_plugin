#booknetic_theme_%%id%% *
{
    font-family: '%%fontfamily%%', sans-serif !important;
}
#booknetic_theme_%%id%%
{
    height: %%height%%px;
}

#booknetic_theme_%%id%% .booknetic_appointment_steps
{
    background: %%panel%%;
}

#booknetic_theme_%%id%% .booknetic_badge
{
    background: %%other_steps%%;
}
#booknetic_theme_%%id%% .booknetic_appointment_steps_footer_txt2
{
    color: %%other_steps%%;
}
#booknetic_theme_%%id%% .booknetic_step_title, #booknetic_theme_%%id%% .booknetic_appointment_steps_footer_txt1
{
    color: %%other_steps_txt%%;
}

#booknetic_theme_%%id%% .booknetic_selected_step > .booknetic_badge::after
{
background-color: %%compleated_steps%%;
}
#booknetic_theme_%%id%% .booknetic_selected_step .booknetic_step_title
{
color: %%compleated_steps_txt%%;
}

#booknetic_theme_%%id%% .booknetic_active_step .booknetic_badge, #booknetic_theme_%%id%% .booknetic_calendar_days > div > span > i[a], #booknetic_theme_%%id%% .booknetic_btn_success
{
    background: %%active_steps%%;
}
#booknetic_theme_%%id%% .booknetic_active_step .booknetic_step_title
{
    color: %%active_steps_txt%%;
}

#booknetic_theme_%%id%% .booknetic_btn_primary,
#booknetic_theme_%%id%% .booknetic_selected_time,
#booknetic_theme_%%id%% .booknetic_calendar_selected_day > div
{
    background: %%primary%% !important;
    color: %%primary_txt%% !important;
}
#booknetic_theme_%%id%% .booknetic_service_category, #booknetic_theme_%%id%% .booknetic_service_extra_title, #booknetic_theme_%%id%% .booknetic_times_title, #booknetic_theme_%%id%% .booknetic_text_primary
{
    color: %%primary%% !important;
}

#booknetic_theme_%%id%% .booknetic_category_accordion .booknetic_service_category span {
    background: %%primary%% !important;
}

#booknetic_theme_%%id%% .booknetic_appointment_container_header
{
    color: %%title%% !important;
}

#booknetic_theme_%%id%% .booknetic_service_card_selected,
#booknetic_theme_%%id%% .booknetic_card_selected,
#booknetic_theme_%%id%% .booknetic_service_extra_card_selected,
#booknetic_theme_%%id%% .booknetic_payment_method_selected,
#booknetic_theme_%%id%% .booknetic-cart-item.active
{
    border-color: %%border%% !important;
}

#booknetic_theme_%%id%% .booknetic_service_card_price,
#booknetic_theme_%%id%% .booknetic_service_extra_card_price,
#booknetic_theme_%%id%% .booknetic_confirm_details_price:not([data-price-id="discount"] .booknetic_confirm_details_price,.booknetic_gift_discount_price),
#booknetic_theme_%%id%% .booknetic-cart-item-body-cell.amount,
#booknetic_theme_%%id%% .booknetic_sum_price
{
    color: %%price%% !important;
}

%%custom_css%%

%%hide_steps%%
