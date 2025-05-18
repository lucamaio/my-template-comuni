<?php if(dci_get_option("area_riservata")) { ?>
   <a class="btn btn-primary btn-icon btn-full" title="Accedi all'area personale" href="<?php echo dci_get_option("area_riservata"); ?>" data-element="personal-area-login">
    <span class="rounded-icon" aria-hidden="true">
        <svg class="icon icon-primary">
            <use xlink:href="#it-user"></use>
        </svg>
    </span>
    <span class="d-none d-lg-block">Accedi all'area personale</span>
  </a>
<?php } else { ?>
   <a class="btn btn-primary btn-icon btn-full" title="Accedi all'area personale" href="<?php echo get_admin_url(); ?>">
    <span class="rounded-icon" aria-hidden="true">
        <svg class="icon icon-primary">
            <use xlink:href="#it-user"></use>
        </svg>
    </span>
    <span class="d-none d-lg-block">Accedi all'area personale</span>
  </a>
<?php } ?>


