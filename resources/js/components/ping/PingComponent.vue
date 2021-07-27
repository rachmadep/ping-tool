<template>
    <b-form inline>
        <label class="mr-sm-3" for="inputHostname">Enter a hostname</label>
        <b-form-input
            id="inputHostname"
            class="mb-2 mr-sm-3 mb-sm-0 col-sm-7"
            placeholder="ex: google.com"
        ></b-form-input>

        <b-button type="button" variant="primary">PING</b-button>
        
    </b-form>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
