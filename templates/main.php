<div class="hero-unit">
    <h1>Bonjour, <?php echo $userDetails["firstname"] ?> <?php echo $userDetails["lastname"] ?> !</h1>
    <br />
    <p>Ton solde payutc est de <strong><?php echo format_amount($userDetails["credit"]) ?> €</strong></p>
</div>
<div class="row">
    <div class="span7" >
        <h2>Historique</h2>
        <div>
            <table id='historique' class='table table-striped'>
                <?php foreach($histo as $elt): ?>
                    <tr>
                        <?php if($elt['type'] == "DEPENSE"): ?>
                            <td><?php echo date('d/m/y H:i:s', $elt[0]) ?></td>
                            <td><?php echo $elt[1] ?> (<?php echo $elt[4] ?>)</td>
                            <td><span class="label label-important"> <i class="icon-minus icon-white"></i> <?php echo format_amount($elt[6]) ?> €</span></td>
                        <?php elseif($elt['type'] == "RECHARGEMENT"): ?>
                            <td><?php echo date('d/m/y H:i:s', $elt[0]) ?></td>
                            <td>Rechargement</td>
                            <td><span class="label label-success"> <i class="icon-plus icon-white"></i> <?php echo format_amount($elt[5]) ?> €</span></td>
                        <?php elseif($elt['type'] == "VIREMENTin"): ?>
                            <td><?php echo date('d/m/y H:i:s', $elt[0]) ?></td>
                            <td>Virement (<?php echo $elt[2] ?> <?php $elt[3]?>)</td>
                            <td><span class="label label-success"> <i class="icon-plus icon-white"></i> <?php echo format_amount($elt[1])?> €</span></td>
                        <?php elseif($elt['type'] == "VIREMENTout"): ?>
                            <td><?php echo date('d/m/y H:i:s', $elt[0]) ?></td><td>Virement (<?php echo $elt[2] ?> <?php echo $elt[3] ?>)</td>
                            <td><span class="label label-important"> <i class="icon-minus icon-white"></i> <?php echo format_amount($elt[1])?> €</span></td>
                        <?php endif ?>
                    </tr>
                <?php endforeach ?>
            </table>
            <div class="pagination pagination-centered"><ul id="paging"></ul></div>
        </div>
    </div>
    <div class="span5">
        <h2>Rechargement</h2>
        <?php if($max_reload != 0): ?>
            <?php if(isset($flash['reload_erreur'])): ?>
                <div class="alert alert-error"><?php echo $flash['reload_erreur'] ?></div>
            <?php endif ?>
            <?php if(isset($flash['reload_ok'])): ?>
                <div class="alert alert-success"><?php echo $flash['reload_ok'] ?></div>
            <?php endif ?>
            <form action="reload" method="post" class="well form-inline">
                <p>
                    <h6>Montant du rechargement</h6><br />
                    <div class="input-prepend input-append">
                        <span class="add-on">€</span><?php echo $max_reload/100?>
                        <?php
                        if(isset($flash['reload_value'])) {
                            $reload_value = $flash['reload_value'];
                        } else {
                            $reload_value = $default_reload_value;
                        }
                        ?>
                        <input name="montant" type="number" class="span1" min="<?php echo $min_reload/100?>" max="200" value="<?php echo $reload_value ?>" step="0.01" />
                        <button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Recharger</button>
                    </div>
                </p>
                    
            </form>
        <?php else: ?>
            <div class="alert alert-success">
                Ton compte ne peut être rechargé sans dépasser le plafond maximum.
            </div>
        <?php endif ?> 
        <h2>Virement à un ami</h2>
        <form action="virement" method="post" class="well form-inline">
            <?php if(isset($flash['virement_ok'])): ?>
                <div class="alert alert-success">
                    <?php echo $flash['virement_ok'] ?>
                </div>
            <?php endif ?>
            <?php if(isset($flash['virement_erreur'])): ?>
                <div class="alert alert-error">
                    <?php echo $flash['virement_erreur'] ?>
                </div>		                
            <?php endif ?>
            <p>
                <h6>Trouver un utilisateur</h6><br />
                <input size="30" id="userName" name="userName" placeholder="Destinataire" type="text" autocomplete="off"/>
                <input id="userId" name="userId" type="hidden" />
            </p>
            <p>
                <h6>Montant du virement</h6><br />
                <div class="input-append">
                    <input name="montant" type="number" class="span1" min="0" max="<?php echo $userDetails["credit"] ?>" value="<?php if(isset($virement_value)) echo $virement_value ?>" step="0.01" />
                    <span class="add-on">€</span>
                    <button type="submit" class="btn btn-primary"><i class="icon-arrow-right icon-white"></i> Transférer</button>
                </div>
            </p>
                    
        </form>
        <h2>Etat du compte
            <?php
            if($isBlocked == 1) {
                echo "<span class=\"label label-important\">Bloqué <i class=\"icon-remove icon-white\"></i></span>";
            } else {
                echo "<span class=\"label label-success\">Débloqué <i class=\"icon-ok icon-white\"></i></span>";
            }
            ?>
        </h2>
        <div class="well">
            En cas de perte ou vol de ton badge, tu peux ici bloquer et débloquer la possibilité de payer avec ta carte.<br />
            <?php
            if($isBlocked == 1) {
                echo "<a class=\"btn btn-success pull-right\" href=\"unblock\">Débloquer mon compte</a>";
            } else {
                echo "<a class=\"btn btn-danger pull-right\" href=\"block\">Bloquer mon compte</a>";
            }
            ?>
            <br />
        </div>
    </div>
</div>