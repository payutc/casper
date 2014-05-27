<div class="jumbotron">
    <h1>Bonjour, <?php echo $userDetails["firstname"] ?> <?php echo $userDetails["lastname"] ?> !</h1>
    <br />
    <p>Ton solde payutc est de <strong><?php echo format_amount($userDetails["credit"]) ?> €</strong></p>
</div>
<div class="row">
    <div class="col-md-4">
        <h2>Rechargement <a name="rechargement" rel="tooltip" data-placement="bottom" data-original-title="Recharger ton compte par Carte Bancaire" class="hidden-phone"><i class="glyphicon glyphicon-question-sign"></i></a></h2>
        <?php if($canReload): ?>
            <?php if(isset($flash['reload_erreur'])): ?>
                <div class="alert alert-danger"><?php echo $flash['reload_erreur'] ?></div>
            <?php endif ?>
            <?php if(isset($flash['reload_ok'])): ?>
                <div class="alert alert-success"><?php echo $flash['reload_ok'] ?></div>
            <?php endif ?>
            <form action="reload" method="post" class="well form-inline">
                <div class="input-group">
                    <?php
                    if(isset($flash['reload_value'])) {
                        $reload_value = $flash['reload_value'];
                    } else {
                        $reload_value = "";
                    }
                    ?>
                    <input name="montant" type="number" placeholder="0,00" class="col-md-1" min="<?php echo $minReload/100?>" max="<?php echo $maxReload/100?>" value="<?php echo $reload_value ?>" step="0.01" />
                    <span class="input-group-addon">€</span>
                </div>
                <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-shopping-cart glyphicon glyphicon-white"></i> Recharger</button>                    
            </form>
        <?php else: ?>
            <div class="alert alert-success">
                Ton compte ne peut être rechargé : <?php echo $cannotReloadMessage ?>
            </div>
        <?php endif ?> 
        <h2>Virement à un ami <a name="virement" rel="tooltip" data-placement="bottom" data-original-title="Transférer gratuitement de l'argent à un autre utilisateur de payutc" class="hidden-phone"><i class="glyphicon glyphicon-question-sign"></i></a></h2>
        <?php if(isset($flash['virement_ok'])): ?>
            <div class="alert alert-success">
                <?php echo $flash['virement_ok'] ?>
            </div>
        <?php endif ?>
        <?php if(isset($flash['virement_erreur'])): ?>
            <div class="alert alert-danger">
                <?php echo $flash['virement_erreur'] ?>
            </div>		                
        <?php endif ?>
        <form action="virement" method="post" class="well form-inline">
            <p>
                <input id="userName" name="userName" placeholder="Destinataire" type="text" autocomplete="off"/>
                <input id="userId" name="userId" type="hidden" />
            </p>
            <p>
                <input name="message" placeholder="Message" type="text" />
            </p>
            <p>
                <div class="input-group">
                    <input name="montant" placeholder="0,00" type="number" class="col-md-1" min="0" max="<?php echo $userDetails["credit"] ?>" step="0.01" />
                    <span class="input-group-addon">€</span>
                </div>
            </p>
            <p>
                <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-arrow-right glyphicon glyphicon-white"></i> Transférer</button>
            </p>
                    
        </form>
        <h2>Blocage badge <a name="virement" rel="tooltip" data-placement="bottom" data-original-title="En cas de perte ou vol de ton badge, tu peux ici bloquer et débloquer son utilisation pour payutc" class="hidden-phone"><i class="glyphicon glyphicon-question-sign"></i></a></h2>
        <?php if(isset($flash['block_erreur'])): ?>
            <div class="alert alert-danger"><?php echo $flash['block_erreur'] ?></div>
        <?php endif ?>
        <div class="well">
            <p>
                État du compte : 
                <?php if($isBlocked): ?>
                    <span class="label label-danger">Bloqué <i class="glyphicon glyphicon-remove glyphicon glyphicon-white"></i></span>
                <?php else: ?>
                <span class="label label-success">Débloqué <i class="glyphicon glyphicon-ok glyphicon glyphicon-white"></i></span>
            <?php endif ?>
        </p>
        <p>
            <?php if($isBlocked): ?>
                <a class="btn btn-success" href="unblock">Débloquer mon compte</a>
            <?php else: ?>
                <a class="btn btn-danger" href="block">Bloquer mon compte</a>
            <?php endif ?>
        </p>
    </div>
</div>
<div class="col-md-8" >
    <h2>Historique</h2>
    <div>
        <table id='historique' class='table table-striped'>
            <?php foreach($historique as $elt): ?>
                <tr>
                    <td>
                        <?php echo date('d/m/y H:i:s', strtotime($elt->date)) ?>
                    </td>
                    <?php if($elt->type == "PURCHASE"): ?>
                        <td>
                            <?php echo $elt->name ?> <small><?php echo $elt->fun ?></small>
                        </td>
                        <td class="debit">
                            - <?php echo format_amount($elt->amount) ?> €
                        </td>
                    <?php elseif($elt->type == "RECHARGE"): ?>
                        <td>
                            Rechargement
                        </td>
                        <td class="credit">
                             + <?php echo format_amount($elt->amount) ?> €
                        </td>
                    <?php elseif($elt->type == "VIRIN"): ?>
                        <td>
                            Virement de <?php echo $elt->firstname ?> <?php echo $elt->lastname ?>
                            <?php if(!empty($elt->name)): ?>
                                 (<?php echo $elt->name ?>)
                             <?php endif ?>
                        </td>
                        <td class="credit">
                            + <?php echo format_amount($elt->amount)?> €
                        </td>
                    <?php elseif($elt->type == "VIROUT"): ?>
                        <td>
                            Virement à <?php echo $elt->firstname ?> <?php echo $elt->lastname ?>
                            <?php if(!empty($elt->name)): ?>
                                 (<?php echo $elt->name ?>)
                             <?php endif ?>
                         </td>
                        <td class="debit">
                            - <?php echo format_amount($elt->amount)?> €
                        </td>
                    <?php endif ?>
                </tr>
            <?php endforeach ?>
        </table>
        <div class="pagination pagination-centered"><ul id="paging"></ul></div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    init();
    selectPage(1);
    $("a").tooltip();
});
</script>
