<?php

class MY_Profiler extends CI_Profiler {

    function MY_Profiler() {

        $CI = & get_instance();
        parent::CI_Profiler();
    }

    // --------------------------------------------------------------------

    /**
     * Compile $_POST Data
     *
     * @access    private
     * @return    string
     */
    function _compile_session() {
        $output = "\n\n";
        $output .= '<fieldset style="border:1px solid #020;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
        $output .= "\n";
        $output .= '<legend style="color:#020;">SESSION DATA</legend>';
        $output .= "\n";

        if (count($this->CI->session->userdata) == 0) {
            $output .= "<div style='color:#009900;font-weight:normal;padding:4px 0 4px 0'>No Session data exists</div>";
        } else {
            $output .= "\n\n<table cellpadding='4' cellspacing='5' border='0' width='100%'>\n";

            foreach ($this->CI->session->userdata as $key => $val) {

                $output .= "<tr><td align='right' width='20%' style='color:#000;background-color:#ddd;padding:4px 10px;'>$key</td><td align='left' width='80%' style='color:#000;font-weight:normal;background-color:#ddd;padding:4px 10px;'>";
                if (is_array($val)) {
                    $output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
                } else {
                    $output .= htmlspecialchars(stripslashes($val));
                }

                $output .= "</td></tr>\n";
            }
        }

        $output .= "</table>\n";

        $output .= "</fieldset>";

        return $output;
    }

    // --------------------------------------------------------------------

    /**
     * Run the Profiler
     *
     * @access    private
     * @return    string
     */
    function run($output = '') {
        $output = '<br clear="all" />';
        $output .= "<div style='background-color:#fff;padding:10px;'>";
        $output .= $this->_compile_benchmarks();
        $output .= $this->_compile_post();
        $output .= $this->_compile_session();
        $output .= $this->_compile_queries();

        $output .= '</div>';

        return $output;
    }

}

?> 