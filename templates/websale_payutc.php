<div class="row">
    <div class="span4 offset4">
        <?php if(isset($flash['websale_error'])): ?>
            <div class="alert alert-error"><?php echo $flash['websale_error'] ?></div>
        <?php endif ?>
            <h1>Bonjour <?php echo $firstname ?></h1>

            <p>Tu vas réaliser un paiement à <em><?php echo $fundation ?></em> via payutc.</p> 
            
            <table class="table table-hover" id="opTable">
                <tbody>
                    <?php foreach($purchases as $purchase): ?>
                    <tr>
                        <td><?php echo $purchase->pur_qte ?> <?php echo $products[$purchase->obj_id]->name ?> à <?php echo format_amount($purchase->pur_unit_price) ?> €</td>
                        <td class="debit"> - <?php echo format_amount($purchase->pur_price) ?> €</td>
                    </tr>
                    <?php endforeach ?>
                    <tr>
                        <td>
                            <b>Total à payer</b>
                        </td>
                        <td class="debit">- <?php echo format_amount($total) ?> €</td>
                    </tr>
                </tbody>
            </table>
            
            <p>Voici les opérations sur ton compte payutc :</p>
            
            <form action="websale" method="post">
                <input type="hidden" name="tra_id" value="<?php echo $_GET['tra_id'] ?>" />
                <input type="hidden" name="token" value="<?php echo $_GET['token'] ?>" />
                <input type="hidden" name="method" value="payutc" />
                <input type="hidden" name="final" id="final" value="<?php echo ($solde-$total)/100 ?>">
            
                <table class="table table-hover" id="opTable">
                    <tbody>
                        <tr>
                            <td>Mon solde actuel</td>
                            <td class="credit"> + <?php echo format_amount($solde) ?> €</td>
                        </tr>
                        <tr>
                            <td>
                                Montant de la transaction
                            </td>
                            <td class="debit">- <?php echo format_amount($total) ?> €</td>
                        </tr>
                        <tr id="reloadLine" data-placement="right" data-content="<?php echo ($total-$solde > 0) ? 'Pour régler cet achat, tu dois d\'abord  recharger de '.format_amount($total-$solde).' €. Tu peux augmenter ce montant pour garder du crédit sur ton compte.' : 'Tu peux recharger ton compte payutc pour ne pas avoir un solde trop bas' ?>"  data-original-title="Rechargement payutc" data-trigger="hover">
                            <?php if($canReload): ?>
                            <td>Rechargement</td>
                            <td style="text-align:right">
                                <div class="input-append">
                                    <input id="montant" name="montant" type="number" placeholder="0,00" class="span1" min="<?php echo ($total-$solde > 0) ? ($total-$solde)/100 : $minReload/100 ?>" max="<?php echo ($maxReload-$total)/100 ?>" value="<?php echo ($total-$solde > 0) ? ($total-$solde)/100 : 0 ?>" step="0.01" />
                                    <span class="add-on">€</span>
                                </div>
                            </td>
                        </tr>
                        <?php endif ?>
                        <tr>
                            <td>Solde final</td>
                            <td class="credit" id="finalAmount"><?php echo ($solde-$total > 0) ? format_amount($solde-$total) : format_amount(0) ?> €</td>
                        </tr>
                    </tbody>
                </table>
               
                <input type="submit" class="btn btn-primary" value="<?php echo ($total-$solde > 0) ? 'Recharger et payer' : 'Payer' ?>" id="submitBut"/>
            </form> 
      </div>
</div>