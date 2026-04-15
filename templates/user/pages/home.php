<div class="row">
    <div class="col-md-12">
        <h1 class="display-4 fw-bold mb-4">Vitajte v užívateľskej sekcii, <?= htmlspecialchars($_SESSION['user']['first_name']) ?>!</h1>
        <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 shadow-sm">
            <div class="card-body p-5">
                <p class="lead mb-4">Toto je vaša osobná nástenka, kde môžete spravovať svoj profil a prezerať si svoje aktivity.</p>
                <hr class="my-4 border-secondary border-opacity-25">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark shadow-sm h-100">
                            <h5 class="text-info">Váš Profil</h5>
                            <p class="small text-secondary">Zobrazte si svoje osobné údaje.</p>
                            <a href="<?= url('user/profile') ?>" class="btn btn-sm btn-outline-info">Prejsť na profil</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark shadow-sm h-100 opacity-50">
                            <h5 class="text-info">Moje Trate</h5>
                            <p class="small text-secondary">Pripravujeme: Zoznam vašich obľúbených tratí.</p>
                            <button disabled class="btn btn-sm btn-outline-secondary">Už čoskoro</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark shadow-sm h-100 opacity-50">
                            <h5 class="text-info">Nastavenia</h5>
                            <p class="small text-secondary">Pripravujeme: Prispôsobte si svoj účet.</p>
                            <button disabled class="btn btn-sm btn-outline-secondary">Už čoskoro</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
