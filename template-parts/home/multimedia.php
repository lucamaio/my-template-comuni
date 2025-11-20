<?php
global $medias,$media;
$box_media = $medias;
$titolo_multimedia = dci_get_option('multimedia_title', 'multimedia') ?: "";
?>


<div class="bg-grey-dsk">
<?php if (!empty($medias)) { ?>
    <div class="container py-5">
        <h2 class="title-xxlarge mb-4"><?php echo $titolo_multimedia; ?></h2>
        <div class="gallery">
            <?php foreach($medias as $box) { ?>
                <div class="video-container">
                    <h4 class="blog-title-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20px" height="auto">
                            <path d="M0 128C0 92.7 28.7 64 64 64l256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64L64 448c-35.3 0-64-28.7-64-64L0 128zM559.1 99.8c10.4 5.6 16.9 16.4 16.9 28.2l0 256c0 11.8-6.5 22.6-16.9 28.2s-23 5-32.9-1.6l-96-64L416 337.1l0-17.1 0-128 0-17.1 14.2-9.5 96-64c9.8-6.5 22.4-7.2 32.9-1.6z" />
                        </svg>
                        <span class="darkblue"><?php echo $box['titolo_video'];?></span>
                    </h4>
                    <?php if(isset($box['link_video'])&&$box['link_video']!=null) {?>
                        <iframe title="YouTube video player" src="<?php echo $box['link_video'];?>" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
                    <?php } elseif(isset($box['video_item']) && $box['video_item'] != null) { ?>
                        <video controls>
                            <source src="<?php echo $box['video_item'];?>" type="video/mp4">
                        </video>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } else{ ?>
    <h4 style="text-align: center">Niente da mostrare</h4>
<?php } ?>
</div>
    
<style>
  .gallery {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    border-radius: 10px;
  } 

  .video-container {
    flex-basis: calc(33.33% - 20px); /* Tre video per riga su schermi più grandi */
    background-color: #ffffff;
    padding: 15px;
    text-align: center;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Ombra più leggera e diffusa */
    transition: transform 0.3s, box-shadow 0.3s; /* Transizione per effetto hover */
  }

  .video-container:hover {
    transform: translateY(-5px); /* Effetto di sollevamento al passaggio del mouse */
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Ombra più marcata al passaggio del mouse */
  }

  .video-container iframe,
  .video-container video {
    width: 100%;
    height: 300px; /* Altezza ridotta per un layout più moderno */
    border-radius: 12px; /* Angoli più arrotondati */
    border: none;
  }

  .blog-title-2 {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
  }

  .blog-title-2 svg {
    margin-right: 10px;
    fill: #007bff;
  }

  .darkblue {
    color: #0056b3;
  }

  @media (max-width: 768px) {
    .video-container {
      flex-basis: calc(50% - 20px); /* Due video per riga su schermi medi */
    }
  }

  @media (max-width: 480px) {
    .video-container {
      flex-basis: 100%; /* Un video per riga su schermi piccoli */
    }
  }
</style>

