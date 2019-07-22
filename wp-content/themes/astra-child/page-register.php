<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

get_header(); ?>

  <div class="entry-content clear" itemprop="text">
    <div class="join-content-column">
      <h1><?php the_title(); ?></h1>
      <?php echo the_content(); ?>
    </div>
    <div class="join-selection-column">
      <h3>Select contribution type</h3>
      <div class="join-selection-type">

        <button class="join-selection-type-item" data-toggle="monthly-options">Monthly</button>
        <button class="join-selection-type-item" data-toggle="yearly-options">Yearly</button>
        <button class="join-selection-type-item" data-toggle="one-time-gift-options">One Time Gift</button>
      </div>
        <div class="join-selection-options-wrap">
          <div id="one-time-gift-options" class="join-selection-options">
            <span class="join-description">Select contribution amount<br/>(membership expires after one year)</span>
            <ul>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/one-time-gift-12/">$12</a></li>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/one-time-gift-25/">$25</a></li>
            </ul>
          </div>

          <div id="yearly-options" class="join-selection-options">
            <span class="join-description">Select yearly contribution amount</span>
            <ul>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/yearly-12/">$12</a></li>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/yearly-25/">$25</a></li>
            </ul>
          </div>

          <div id="monthly-options" class="join-selection-options">
            <span class="join-description">Select monthly contribution amount</span>
            <ul>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/monthly-5/">$5</a></li>
              <li><a class="join-selection-amount" href="https://stbetterangels.wpengine.com/register/monthly-10/">$10</a></li>
            </ul>
          </div>
        </div>

      <form id="join-submit-form" action="">
        <input id="join-submit-button" type="submit" value="Join" disabled="disabled" />
      </form>
    </div>

  </div>


<style>
  .entry-content {
    margin: auto;
    display: block;
    max-width: 1200px;
    margin-top: 2rem;
    margin-bottom: 2rem;
  }
  @media screen and (min-width: 900px) {
    .join-content-column, .join-selection-column {
      padding: 1rem;
      width: 60%;
      float: left;
      display: inline-block;
    }
    .join-selection-column {
      min-height: 300px;
      width: 40%;
      border: 1px solid lightgray;
    }
    .join-selection-type-item {
      padding: 10px 10px;
      min-width: 32.5%;
      margin-bottom: 1rem;
    }
    .screen-reader-label {
      visibility: hidden;
      height: 0px;
      position: absolute;
    }
  }
  .join-selection-options-wrap {
    min-height: 100px;
  }
  .join-selection-options {
    display: none;
  }
  .join-selection-options.active {
    display: block;
  }
  .join-selection-options ul {
    list-style: none;
    margin-left: 0;
    display: flex;
    flex-grow: 1;
  }
  .join-selection-options ul li {
    display: inline-block;
    flex: 1;
  }
  .join-selection-options ul li a{
    border-radius: 2px;
    display: block;
    text-align: center;
    width: 100%;
    padding: 10px 40px;
    color: #ffffff;
    border-color: #23356c;
    background-color: #23356c;
    display: inline-block;
  }
  .join-selection-options ul li a:hover,
  .join-selection-options ul li a:focus,
  .join-selection-options ul li a:active,
  .join-selection-type button.selected,
  .join-selection-amount.selected {
    color: #ffffff;
    border-color: #ab2634;
    background-color: #ab2634;
  }
  .join-description {
    padding-bottom: 1rem;
    display: inline-block;
  }
  .join-selection-type {}
  #join-submit-button {
    width: 100%;
  }
  #join-submit-button:disabled {
    background: #8e8e8e;
    cursor: not-allowed;
  }
</style>
<noscript>
  <style>
    .screen-reader-label {
      visibility: visible;
      height: auto;
      position: static;
    }
    .join-selection-options ul {
      display: block;
    }
  </style>
</noscript>
<script>

  function handleSelectPaymentType() {
    document.addEventListener('click', function (event) {
      // If the clicked element doesn't have the right selector, bail
      if (!event.target.matches('.join-selection-type button')) return;
      // Don't follow the link
      event.preventDefault();

      var selectedId = event.target.getAttribute('data-toggle');
      var options = document.getElementsByClassName('join-selection-type-item');
      for (var i = 0; i < options.length; i++) {
        if (options[i].classList.contains('selected') ) {
          options[i].classList.remove('selected');
        }
      }
      event.target.classList.add('selected');
      togglePaymentType(selectedId);
      togglePaymentAmount(false);
    }, false);
  }


  function togglePaymentType(id) {
    var options = document.getElementsByClassName('join-selection-options');
    for (var i = 0; i < options.length; i++) {
      if (options[i].classList.contains('active') && options[i].id != id ) {
        options[i].classList.remove('active');
      } else if (!options[i].classList.contains('active') && options[i].id == id) {
        options[i].classList.add('active');
      }
    }
    resetSelectedAmount();
  }

  function handleSelectPaymentAmount() {
    document.addEventListener('click', function (event) {
      // If the clicked element doesn't have the right selector, bail
      if (!event.target.matches('.join-selection-amount')) return;
      // Don't follow the link
      event.preventDefault();

      resetSelectedAmount();

      event.target.classList.add('selected');

      var selectedUrl = event.target.getAttribute('href');

      togglePaymentAmount(selectedUrl);
    }, false);
  }


  function togglePaymentAmount(url) {
    if (!url) {
      document.getElementById('join-submit-form').setAttribute('action', '');
      document.getElementById('join-submit-button').setAttribute('disabled', 'disabled');
    } else {
      document.getElementById('join-submit-form').setAttribute('action', url);
      document.getElementById('join-submit-button').removeAttribute('disabled');
    }
  }

  function resetSelectedAmount() {
    var options = document.getElementsByClassName('join-selection-amount');
    for (var i = 0; i < options.length; i++) {
      if (options[i].classList.contains('selected') ) {
        options[i].classList.remove('selected');
      }
    }
  }

  function init() {
    handleSelectPaymentType();
    handleSelectPaymentAmount();
  }

  init();

</script>
<?php get_footer(); ?>
