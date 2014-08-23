<div class="row">
    <div class="col-md-12">
        <?php if(isset($flash['register_erreur'])): ?>
            <div class="alert alert-danger"><?php echo $flash['register_erreur'] ?></div>
        <?php endif ?>
        <div class="well">
            <h1>Bienvenue sur payutc</h1>
            <p><em>Pour continuer à utiliser le service, vous devez lire et accepter les conditions suivantes.</em></p>
        
            <p>Les informations recueillies par payutc sont nécessaires au fonctionnement de votre porte monaie électronique. Elles font l'objet d'un traitement informatique et sont destinées à l'association BDE-UTC. Conformément à la loi « informatique et libertés » du 6 janvier 1978 modifiée en 2004, vous bénéficiez d'un droit d'accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer ce droit et obtenir communication des informations vous concernant, veuillez vous adresser à BDE-UTC payutc par e-mail.</p>
            <p>Dans le cadre du fonctionnement de payutc, vos informations peuvent être retransmises aux différentes associations utilisant le système pour encaisser des paiements.</p>
        
            <p>Votre historique de consommation est conservé jusqu'à un an après votre dernière utilisation de payutc et votre solde d'argent jusqu'à deux ans après votre dérnière utilisation de payutc. Après votre départ de l'UTC, ce délai peut être réduit à six mois. À l'expiration de ce délai, le solde d'argent conservé sur payutc est perdu.</p>
        
            <p>payutc est hébergé sur le réseau. Son utilisation génère donc des traces pouvant être utilisées en cas de menace sur la sécurité du réseau, ou de demande des autorités compétentes, à des fins d’identification d’actions illicites et de leurs auteurs. Pour plus de détails, nous vous invitons à consulter la <a href="http://www.utc.fr/wiki/charte_informatique/charte.pdf" target="_blank">Charte du bon usage des moyens et ressources informatiques</a>. Ce document rappelle également que l'article L.323-1 du Nouveau code pénal prévoit que « le fait d’accéder ou de se maintenir, frauduleusement, dans tout ou partie d’un système de traitement automatisé de données est puni d’un an d’emprisonnement et de 15 000 € d’amende ». Dans le cas de l'introduction, la suppression ou la modification frauduleuses de données dans un système informatique, la peine est portée à trois ans 
    d'emprisonnement et 45 000 € d'amende.</p>

            <?php if($form) { ?>
            <form action="register" method="post">
                <input type="submit" class="btn btn-primary btn-large" value="J'accepte" />
            </form> 
            <?php } ?>
        </div>
    </div>
</div>
