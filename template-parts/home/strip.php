<?php
$strip = get_option('strip_home');

if (!empty($strip['strip_items']) && count($strip['strip_items']) > 0) :
?>

<section class="strip">

  <button class="strip-arrow left">&#10094;</button>

  <div class="strip-inner" id="stripScroll">

    <?php foreach ($strip['strip_items'] as $item) : 
        $target = (!empty($item['blank'])) ? ' target="_blank"' : '';
    ?>

      <div class="strip-item">
        <a href="<?php echo esc_url($item['url']); ?>" <?php echo $target; ?>>

          <div class="strip-icon">
            <?php if (!empty($item['icon'])): ?>
              <i class="<?php echo esc_attr($item['icon']); ?>"></i>
            <?php endif; ?>
          </div>

          <div class="strip-text">
            <strong><?php echo esc_html($item['title']); ?></strong>
            <?php echo esc_html($item['desc']); ?>
          </div>

        </a>
      </div>

    <?php endforeach; ?>

  </div>

  <button class="strip-arrow right">&#10095;</button>

</section>

<?php endif; ?>


<style>

/* ===== STRIP ===== */
.strip {
  position: relative;
  background: var(--bs-primary, #980847);
  transform: skewY(-3deg);
  margin: 80px 0 140px;
  padding: 60px 0;
  z-index: 1;
}

/* CONTENUTO */
.strip .strip-inner {
  transform: skewY(3deg);
  display: flex;
  align-items: center;
  gap: 40px;
  max-width: 1200px;
  margin: auto;
  color: #fff;
  text-align: center;
  overflow: hidden;
}

/* 4 elementi visibili */
.strip .strip-item {
  flex: 0 0 calc(25% - 30px);
  max-width: calc(25% - 30px);
}

/* ICONA */
.strip .strip-icon {
  font-size: 40px;
  margin-bottom: 12px;
  height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* TESTO */
.strip .strip-text strong {
  display: block;
  font-size: 16px;
  margin-bottom: 5px;
}

.strip .strip-text {
  font-size: 13px;
  opacity: 0.9;
}

/* LINK */
.strip a {
  color: #fff;
  text-decoration: none;
}

.strip a:hover {
  color: #f1f1f1;
}

/* FRECCE */
.strip-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,0,0,0.3);
  border: none;
  color: #fff;
  font-size: 22px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  z-index: 3;
}

.strip-arrow.left { left: 10px; }
.strip-arrow.right { right: 10px; }

.strip-arrow:hover {
  background: rgba(0,0,0,0.5);
}

/* OMBRA */
.strip::after {
  content: "";
  position: absolute;
  left: 10%;
  right: 10%;
  bottom: -20px;
  height: 25px;
  background: rgba(0,0,0,0.3);
  filter: blur(12px);
  border-radius: 100px;
  pointer-events: none;
}

/* MOBILE */
@media (max-width: 768px) {

  /* togli effetto slider */
  .strip .strip-inner {
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow: visible;
    align-items: stretch;
  }

  /* ogni elemento full width */
  .strip .strip-item {
    flex: 0 0 100%;
    max-width: 100%;
  }

  /* stile tipo card */
  .strip .strip-item a {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255,255,255,0.08);
    padding: 15px;
    border-radius: 12px;
  }

  /* icona a sinistra */
  .strip .strip-icon {
    font-size: 28px;
    margin: 0;
  }

  /* testo a sinistra */
  .strip .strip-text {
    text-align: left;
  }

  /* NASCONDI FRECCE */
  .strip-arrow {
    display: none !important;
  }

}

</style>


<script>

document.addEventListener("DOMContentLoaded", function() {

  if (window.innerWidth <= 768) return; // 🔥 BLOCCA SU MOBILE

  const container = document.getElementById("stripScroll");
  const items = container?.querySelectorAll(".strip-item");

  if (!container || !items || items.length === 0) return;

  const gap = 40;
  const itemWidth = items[0].offsetWidth + gap;
  const scrollAmount = itemWidth * 4;

  const left = document.querySelector(".strip-arrow.left");
  const right = document.querySelector(".strip-arrow.right");

  left.onclick = () => container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
  right.onclick = () => container.scrollBy({ left: scrollAmount, behavior: 'smooth' });

  if (items.length <= 4) {
    left.style.display = "none";
    right.style.display = "none";
  }

});
</script>
