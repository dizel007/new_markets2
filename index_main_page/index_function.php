<?php
/***************************************************************************************************************
* Функция отрисовывает один блок на главном экране
****************************************************************************************************************/

function print_one_block ($main_class, $href_link, $main_name, $discription, $link_pics) {

echo <<<HTML
    <div class="$main_class">
        <a href="$href_link">
            <div class ="left_text">
                <div class = "main_name_table_element">$main_name</div>
                <div class = "dop_name_table_element">$discription</div>
            </div>

            <div class="right_image">
                <p class="aligncenter">

                    <img class="img_new" src="$link_pics" alt="$main_name">

                </p>
        
     </div>
     </a>
    </div>
HTML;
}