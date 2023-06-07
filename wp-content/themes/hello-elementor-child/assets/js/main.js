import { variation  } from "./variation";
import { select  } from "./select";
import { slider  } from "./slider";
import { file  } from "./file";
import { scroll  } from "./scroll";

	if ($('#commentform').length > 0) {
		$('#commentform')[0].encoding = 'multipart/form-data';
	}
    $(document).on('click','.toggle-review-form',function(){
        $('#review_form_wrapper').toggleClass('hide');
    })
variation();
select();
slider();
file();
scroll();