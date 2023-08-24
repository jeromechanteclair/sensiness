import { variation  } from "./variation";
import { select  } from "./select";
import { slider  } from "./slider";
import { file  } from "./file";
import { scroll  } from "./scroll";
import { scrollbar  } from "./scrollbar";
import { cart  } from "./cart";

	if ($('#commentform').length > 0) {
		$('#commentform')[0].encoding = 'multipart/form-data';
	}
    $(document).on('click','.toggle-review-form',function(){
        $('#review_form_wrapper').toggleClass('hide');
    })
    
    $(document).find('.body-overlay').addClass('fade');
    setTimeout(() => {
          $(document).find('.body-overlay').addClass('hide');
    }, 300);
    cart();
    scrollbar();
variation();
select();
slider();
file();
scroll();