
<tr valign="top">
  <th scope="row" class="titledesc">
  	<label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>

  	<?php echo $this->get_tooltip_html($data); ?>
  </th>
  <td class="forminp">

    <fieldset style="background:inherit">
        <div id="detrack-attribute-mapping-tabs" style="background:inherit;border:0px">

          <input type="hidden" name="<?php echo esc_attr($field); ?>" id="detrack-attribute-mapping-master-value" value="<?php echo esc_attr(json_encode($loadedSettings)); ?>"></input>
          <!-- Temporarily hide Expert mode, only release in future updates
               If you read this, feel free to re-enable this
          !-->
          <ul style="background-color:#e5e5e5;;border:0px;display:none">
            <li style="border:1px;background-color:#e5e5e5;"><a href="#detrack-attribute-mapping-easy">Easy modo</a></li>
            <li style="border:1px;background-color:#e5e5e5;"><a href="#detrack-attribute-mapping-expert">Expert modo</a></li>
          </ul>
          <div id="detrack-attribute-mapping-easy" style="margin:0px;padding:0px;border:0px;">
            <table class="wc_gateways widefat">
              <thead>
                <tr>
                  <th>Detrack Delivery Attribute</th>
                  <th>Preset</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($loadedSettings as $attr => $formula) {
                    //determine whether easy mode should be disabled
                    $disableEasy = true;
                    //first, see if this is a built in setting or user added setting
                    if (isset(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$attr])) {
                        //this is a built in setting. compare against mapping table presets.
                        if (in_array($formula, array_column(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$attr]['presets'], 'value'))) {
                            $disableEasy = false;
                        } else {
                            $disableEasy = true;
                        }
                    } else {
                        //this is a custom setting. compare against generic formulae.
                        if (in_array($formula, \Detrack\DetrackWoocommerce\MappingTablePresets::getGenericFormulae())) {
                            $disableEasy = false;
                        } else {
                            $disableEasy = true;
                        }
                    } ?>
                  <tr>
                    <td><?php echo $attr; ?></td>
                    <td>
                      <select data-field="<?php echo $attr; ?>" <?php echo $disableEasy ? 'disabled="disabled"' : ''; ?>>
                        <?php
                        if (isset(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$attr]['presets'])) {
                            foreach (\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$attr]['presets'] as $preset) {
                                if ($attr == 'instructions') {
                                    //$this->log(json_encode($preset));
                                } ?>
                            <option value="<?php echo $preset['value']; ?>" <?php echo ($preset['value'] == trim($formula) || ((trim($formula) == '' || $disableEasy) && isset($preset['default']) && $preset['default'] == 'true')) ? 'selected="selected"' : ''; ?>><?php echo $preset['name']; ?></option>
                          <?php
                            }
                        } else {
                            //$this->log('Generic Formula used!');
                            foreach (\Detrack\DetrackWOocommerce\MappingTablePresets::getGenericFormulae() as $genericFormula) {
                                ?>
                            <option value="<?php echo $genericFormula; ?>" <?php echo ($genericFormula == trim($formula) || ((trim($formula) == '' || $disableEasy) && isset($preset['default']) && $preset['default'] == 'true')) ? 'selected="selected"' : ''; ?>><?php echo $genericFormula; ?></option>
                            <?php
                            }
                        } ?>
                      </select>
                      <?php
                      if ($disableEasy) {
                          ?>
                        <span><br>Custom code written - see expert mode</span>
                      <?php
                      } ?>
                    </td>
                  </tr>
                <?php
                }
                ?>
                <!-- Template for adding new attributes in front-end!-->
                <tr style="display:none">
                  <td>Testing</td>
                  <td>
                    <select>
                      <?php
                      foreach (\Detrack\DetrackWOocommerce\MappingTablePresets::getGenericFormulae() as $genericFormula) {
                          ?>
                        <option value="<?php echo $genericFormula; ?>"><?php echo $genericFormula; ?></option>
                        <?php
                      }
                      ?>
                    </select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div id="detrack-attribute-mapping-expert" style="">
            <div><h4>Use formulae to implement your custom logic in representing how the data should be modified and formatted before being sent to Detrack. Only change this if you know what you're doing!</h4></div>
            <div id="detrack-attribute-mapping-expert-accordion">
              <?php
              foreach ($loadedSettings as $setting => $formula) {
                  ?>
                <h3>
                  <?php
                  if (!isset(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['protected']) || \Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['protected'] != 'true') {
                      ?>
                    <span class="detrack-delete-attribute-icon"><i class="fas fa-trash-alt"></i></span>
                  <?php
                  } ?>
                    <?php echo $setting; ?>
                </h3>
                <div>
                  <div class="detrack-attribute-mapping-expert-accordion-col-left">
                  <?php
                  //determine if expert mode should be disabled
                  $disableExpert = true;
                  //first, see if this is a built in setting or user added setting
                  if (isset(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['presets'])) {
                      //this is a built in setting. compare against mapping table presets.
                      if (in_array($formula, array_column(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['presets'], 'value'))) {
                          $disableExpert = true;
                      } else {
                          $disableExpert = false;
                      }
                  } else {
                      //this is a custom setting. compare against generic formulae.
                      if (in_array($formula, \Detrack\DetrackWoocommerce\MappingTablePresets::getGenericFormulae())) {
                          $disableExpert = true;
                      } else {
                          $disableExpert = false;
                      }
                  } ?>
                    <textarea class="detrack-attribute-mapping-expert-code" data-field="<?php echo $setting; ?>" rows="7" <?php echo $disableExpert ? 'disabled="disabled"' : ''; ?>><?php echo $formula; ?></textarea>
                    <span class="detrack-attribute-mapping-expert-code-preset-code-warning" style="<?php echo !$disableExpert ? 'display:none' : ''; ?>"><a><i class="fas fa-lock"></i></a>Preset Code - click on lock to make changes</span>
                    <span class="detrack-attribute-mapping-expert-code-custom-code-info" style="<?php echo $disableExpert ? 'display:none' : ''; ?>"><a><i class="fas fa-lock-open"></i></a>Custom Code in use - click on lock to discard changes and reset to default</span>
                  </div>
                  <div class="detrack-attribute-mapping-expert-accordion-col-right">
                    <div class="detrack-attribute-mapping-expert-instructions">
                        <h2 style="font-size:default">Local variables</h2>
                        <ul style="list-style-type:circle;margin-left:20px">
                        <?php
                        if (isset(\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['variables'])) {
                            foreach (\Detrack\DetrackWoocommerce\MappingTablePresets::getData()[$setting]['variables'] as $localVariableName) {
                                ?>
                            <li><?php echo $localVariableName; ?></li>
                          <?php
                            }
                        } else {
                            ?>
                          <i>Nothing</i>
                          <?php
                        } ?>
                      </ul>
                    </div>
                    <div class="detrack-attribute-mapping-expert-instructions">
                      <h2 style="font-size:default">Global variables</h2>
                      <ul style="list-style-type:circle;margin-left:20px">
                        <li>checkoutDate</li>
                        <li>order</li>
                      </ul>
                    </div>
                    <div style="clear:both">
                    </div>
                    <div class="detrack-attribute-mapping-expert-instructions" style="width:100%;display:block">
                      <h2 style="font-size:default">Test console</h2>
                      <div>
                        Test Order ID<input type="text" size="3" value="<?php echo $defaultTestOrder->get_id(); ?>"/> <button class="button detrack-attribute-mapping-expert-test" type="button">Test</button><img src="<?php echo plugin_dir_url(__FILE__).'../img/loading.gif'; ?>" width="25px" height="25px" style="display:none"/>
                      </div>
                      <div>Output:<pre class="detrack-attribute-mapping-expert-console-output"></pre></div>
                    </div>
                  </div>
                  <div style="clear:both">
                  </div>
                </div>
              <?php
              }
              ?>
            </div>
          </div>

          <div style="padding-top:10px">
            <span>Add new attribute:</span>
            <select id="detrack-attribute-add-select">
              <?php
              /**
               * The list of detrack delivery attribtues we will allow the user to modify.
               * Unfortunately, this is hardcoded for now......
               * Attributes that are forbidden to be modified: notify_url, do.
               */
              ?>
              <option value='delivery_time'>delivery_time</option>
              <option value='status'>status</option>
              <option value='open_job'>open_job</option>
              <option value='offer'>offer</option>
              <option value='start_date'>start_date</option>
              <option value='sync_time'>sync_time</option>
              <option value='time'>time</option>
              <option value='time_slot'>time_slot</option>
              <option value='req_date'>req_date</option>
              <option value='track_no'>track_no</option>
              <option value='order_no'>order_no</option>
              <option value='job_type'>job_type</option>
              <option value='job_order'>job_order</option>
              <option value='job_fee'>job_fee</option>
              <option value='addr_company'>addr_company</option>
              <option value='addr_1'>addr_1</option>
              <option value='addr_2'>addr_2</option>
              <option value='addr_3'>addr_3</option>
              <option value='postal_code'>postal_code</option>
              <option value='city'>city</option>
              <option value='state'>state</option>
              <option value='country'>country</option>
              <option value='billing_add'>billing_add</option>
              <option value='name'>name</option>
              <option value='phone'>phone</option>
              <option value='sender_phone'>sender_phone</option>
              <option value='fax'>fax</option>
              <option value='instructions'>instructions</option>
              <option value='assign_to'>assign_to</option>
              <option value='notify_email'>notify_email</option>
              <option value='zone'>zone</option>
              <option value='customer'>customer</option>
              <option value='acc_no'>acc_no</option>
              <option value='owner_name'>owner_name</option>
              <option value='invoice_no'>invoice_no</option>
              <option value='invoice_amt'>invoice_amt</option>
              <option value='pay_mode'>pay_mode</option>
              <option value='pay_amt'>pay_amt</option>
              <option value='group_name'>group_name</option>
              <option value='src'>src</option>
              <option value='wt'>wt</option>
              <option value='cbm'>cbm</option>
              <option value='boxes'>boxes</option>
              <option value='cartons'>cartons</option>
              <option value='pcs'>pcs</option>
              <option value='envelopes'>envelopes</option>
              <option value='pallets'>pallets</option>
              <option value='bins'>bins</option>
              <option value='trays'>trays</option>
              <option value='bundles'>bundles</option>
              <option value='att_1'>att_1</option>
              <option value='depot'>depot</option>
              <option value='depot_contact'>depot_contact</option>
              <option value='sales_person'>sales_person</option>
              <option value='identification_no'>identification_no</option>
              <option value='bank_prefix'>bank_prefix</option>
              <option value='reschedule'>reschedule</option>
              <option value='pod_at'>pod_at</option>
              <option value='reason'>reason</option>
              <option value='ITEM-LEVEL'>ITEM-LEVEL</option>
              <option value='sku'>sku</option>
              <option value='po_no'>po_no</option>
              <option value='batch_no'>batch_no</option>
              <option value='expiry'>expiry</option>
              <option value='desc'>desc</option>
              <option value='cmts'>cmts</option>
              <option value='qty'>qty</option>
              <option value='uom'>uom</option>
            </select>
            <button class="button-primary" id="detrack-attribute-add-new"><i class="fas fa-plus" style="padding-right:5px"></i>Add</button>
            <button class="button" id="detrack-attribute-reset" style="background-color:#f1ebb8">Reset to default</button>
          </div>
        </div>
    </fieldset>
  </td>
</tr>

<?php
