<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <?php if(isset($flash['websale_error'])): ?>
            <div class="alert alert-danger"><?php echo $flash['websale_error'] ?></div>
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
            
            <form action="validation" method="post">
                <input type="hidden" name="tra_id" value="<?php echo $_GET['tra_id'] ?>" />
                <input type="hidden" name="token" value="<?php echo $_GET['token'] ?>" />
                <input type="hidden" name="method" value="payutc" />
                <input type="hidden" name="final" id="final" value="<?php echo ($solde-$total)/100 ?>">
            
                <table class="table table-hover" id="opTable">
                    <tbody>
                        <tr>
                            <td>Solde actuel</td>
                            <td class="credit">+ <?php echo format_amount($solde) ?> €</td>
                        </tr>
                        <tr>
                            <td>
                                Montant de la transaction
                            </td>
                            <td class="debit">- <?php echo format_amount($total) ?> €</td>
                        </tr>
                        <?php
                        $minChamp = $minReload;
                        if($total > $solde && $total-$solde > $minReload){
                            $minChamp = $total-$solde;
                        }
                        $maxChamp = $maxReload+$total;
                        ?>
                        <tr id="reloadLine" data-placement="right" data-content="<?php echo ($total-$solde > 0) ? 'Pour régler cet achat, tu dois d\'abord  recharger de '.format_amount($minChamp).' €. Tu peux augmenter ce montant pour garder du crédit sur ton compte.' : 'Tu peux recharger ton compte payutc au cours de cette opération' ?>"  data-original-title="Rechargement payutc" data-trigger="hover">
                            <?php if($canReload): ?>
                            <td>
                                <?php if($solde >= $total): ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="reload" id="reload" />
                                        Rechargement
                                    </label>
                                </div>
                                <?php else: ?>
                                    Rechargement
                                <?php endif ?>
                            </td>
                            <td style="text-align:right">
                                <div class="input-group">

                                    <input id="montant" name="montant" type="number" placeholder="0,00" class="form-control" min="<?php echo $minChamp/100 ?>" max="<?php echo $maxChamp/100 ?>" value="<?php echo ($total > $solde) ? $minChamp/100 : 0 ?>" step="0.01"<?php echo ($total > $solde) ? '' : ' disabled="disabled"' ?> />
                                    <span class="input-group-addon">€</span>
                                </div>
                            </td>
                            <?php else: ?>
                            <td colspan="2">
                                Rechargement
                                <div class="alert alert-danger">
                                    <?php echo $cannotReloadMessage ?><br />
                                </div>
                            </td>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <td>Solde final</td>
                            <td class="credit" id="finalAmount"><?php echo ($total > $solde) ? format_amount($solde+$minChamp-$total) : format_amount($solde-$total) ?> €</td>
                        </tr>
                    </tbody>
                </table>
                <?php if($solde >= $total || $canReload): ?>
                <input type="submit" class="btn btn-primary" value="<?php echo ($total-$solde > 0) ? 'Recharger et payer' : 'Payer' ?>" id="submitBut"/>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Tu ne peux pas terminer cette transaction car ton solde est trop faible et tu ne peux pas recharger.
                    </div>
                    <a class="btn btn-primary" href="<?php echo $logoutUrl ?>" title="Paiement transaction">Se déconnecter et choisir un autre moyen de paiement</a>
                <?php endif ?>
            </form> 
      </div>
</div>